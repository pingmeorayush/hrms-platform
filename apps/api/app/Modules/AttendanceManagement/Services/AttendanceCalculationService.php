<?php

namespace App\Modules\AttendanceManagement\Services;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type AttendanceRecalculationPayload array{
 *   date_from: string,
 *   date_to: string,
 *   employee_id?: int|string
 * }
 */
class AttendanceCalculationService
{
    public function __construct(
        private readonly AttendanceConfigurationService $attendanceConfigurationService,
        private readonly AttendanceContextResolver $attendanceContextResolver,
    ) {}

    public function calculateRecord(AttendanceRecord $record): AttendanceRecord
    {
        $record->loadMissing('employee.company');

        /** @var Employee $employee */
        $employee = $record->employee;
        $policy = $this->attendanceConfigurationService->getOrCreatePolicy();
        $companyTimezone = $this->resolveEmployeeTimezone($employee);
        $attendanceDate = $this->resolveCarbonValue($record->attendance_date, $companyTimezone)->startOfDay();
        $checkInAt = $this->resolveCarbonValue($record->check_in_at, $companyTimezone);
        $checkOutAt = $this->resolveCarbonValue($record->check_out_at, $companyTimezone);
        $attendanceDateString = $attendanceDate->toDateString();
        $schedule = $this->attendanceContextResolver->resolveScheduleForDate($employee, $attendanceDateString);
        $holiday = $this->attendanceContextResolver->resolveHolidayForDate($employee, $attendanceDateString);
        $approvedLeaveRequest = $this->resolveApprovedLeaveForDate($employee, $attendanceDateString);
        $nonWorkingDays = $this->resolveNonWorkingDays($policy->weekend_rule);

        $isWeekend = in_array(
            $attendanceDate->dayOfWeek,
            $nonWorkingDays,
            true,
        );

        $scheduledStartAt = $schedule['scheduled_start_at'];
        $scheduledEndAt = $schedule['scheduled_end_at'];
        $scheduledWorkMinutes = $schedule['scheduled_work_minutes'] ?? (int) $policy->working_hours_minutes;
        $breakDurationMinutes = (int) $schedule['break_duration_minutes'];
        $lateThresholdMinutes = max(
            (int) $policy->grace_minutes,
            (int) $policy->late_after_minutes,
            (int) ($schedule['shift']->grace_minutes ?? 0),
        );

        $primaryStatus = 'absent';
        $workedMinutes = null;
        $isLate = false;
        $lateMinutes = 0;
        $isHalfDay = false;
        $overtimeMinutes = 0;
        $isEarlyDeparture = false;
        $earlyDepartureMinutes = 0;

        if ($checkInAt !== null && $scheduledStartAt !== null && $checkInAt->gt($scheduledStartAt->copy()->addMinutes($lateThresholdMinutes))) {
            $isLate = true;
            $lateMinutes = $scheduledStartAt->diffInMinutes($checkInAt);
        }

        if ($checkInAt !== null && $checkOutAt === null) {
            $primaryStatus = 'incomplete';
        } elseif ($checkInAt !== null) {
            $rawWorkedMinutes = $checkInAt->diffInMinutes($checkOutAt);
            $workedMinutes = max(0, $rawWorkedMinutes - $breakDurationMinutes);
            $isHalfDay = $workedMinutes < (int) $policy->half_day_minutes;

            if ($scheduledEndAt !== null && $checkOutAt->lt($scheduledEndAt)) {
                $isEarlyDeparture = true;
                $earlyDepartureMinutes = $checkOutAt->diffInMinutes($scheduledEndAt);
            }

            if ($policy->overtime_eligible) {
                $overtimeThreshold = (int) ($policy->overtime_after_minutes ?? $scheduledWorkMinutes);
                $overtimeMinutes = max(0, $workedMinutes - $overtimeThreshold);
            }

            if ($holiday) {
                $primaryStatus = 'holiday';
            } elseif ($isWeekend) {
                $primaryStatus = 'weekend';
            } elseif ($isHalfDay) {
                $primaryStatus = 'half_day';
            } else {
                $primaryStatus = 'present';
            }
        } else {
            $workedMinutes = 0;

            if ($holiday) {
                $primaryStatus = 'holiday';
            } elseif ($isWeekend) {
                $primaryStatus = 'weekend';
            } elseif ($approvedLeaveRequest) {
                $primaryStatus = 'leave';
            }
        }

        $record->forceFill([
            'shift_id' => $schedule['shift']?->id,
            'shift_roster_id' => $schedule['shift_roster']?->id,
            'worked_minutes' => $workedMinutes,
            'primary_status' => $primaryStatus,
            'scheduled_start_at' => $scheduledStartAt,
            'scheduled_end_at' => $scheduledEndAt,
            'scheduled_work_minutes' => $scheduledWorkMinutes,
            'break_duration_minutes' => $breakDurationMinutes,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'is_half_day' => $isHalfDay,
            'overtime_minutes' => $overtimeMinutes,
            'is_weekend' => $isWeekend,
            'is_holiday' => $holiday !== null,
            'holiday_name' => $holiday?->name,
            'is_early_departure' => $isEarlyDeparture,
            'early_departure_minutes' => $earlyDepartureMinutes,
            'calculated_at' => now($companyTimezone),
            'calculation_metadata' => array_filter([
                'schedule_source' => $schedule['schedule_source'],
                'holiday_calendar_id' => $holiday?->holiday_calendar_id,
                'holiday_type' => $holiday?->type,
                'leave_request_id' => $approvedLeaveRequest?->id,
                'leave_type_id' => $approvedLeaveRequest?->leave_type_id,
                'late_threshold_minutes' => $lateThresholdMinutes,
                'overtime_threshold_minutes' => $policy->overtime_eligible
                    ? (int) ($policy->overtime_after_minutes ?? $scheduledWorkMinutes)
                    : null,
                'policy_snapshot' => [
                    'working_hours_minutes' => $policy->working_hours_minutes,
                    'grace_minutes' => $policy->grace_minutes,
                    'late_after_minutes' => $policy->late_after_minutes,
                    'half_day_minutes' => $policy->half_day_minutes,
                    'overtime_eligible' => $policy->overtime_eligible,
                    'overtime_after_minutes' => $policy->overtime_after_minutes,
                    'weekend_rule' => $policy->weekend_rule,
                ],
            ], fn (mixed $value): bool => $value !== null),
        ]);

        $record->save();

        return $record->fresh(['employee', 'shift']);
    }

    private function resolveApprovedLeaveForDate(Employee $employee, string $date): ?LeaveRequest
    {
        return LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('start_date', '<', $this->nextDate($date))
            ->where('end_date', '>=', $date)
            ->orderByDesc('approved_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param  AttendanceRecalculationPayload  $payload
     * @return array{processed:int, created:int, updated:int, skipped:int}
     */
    public function recalculate(User $actor, array $payload): array
    {
        return DB::transaction(function () use ($actor, $payload): array {
            $companyTimezone = $this->resolveUserTimezone($actor);
            $dateFrom = Carbon::parse($payload['date_from'], $companyTimezone)->startOfDay();
            $dateTo = Carbon::parse($payload['date_to'], $companyTimezone)->startOfDay();
            $today = now($companyTimezone)->startOfDay();
            $dates = [];

            foreach (CarbonPeriod::create($dateFrom, $dateTo) as $date) {
                if (! $date instanceof \DateTimeInterface) {
                    continue;
                }

                $dates[] = Carbon::instance($date)->startOfDay();
            }

            $employees = Employee::query()
                ->when(
                    array_key_exists('employee_id', $payload),
                    fn ($query) => $query->whereKey($payload['employee_id']),
                )
                ->orderBy('id')
                ->get();

            $summary = [
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
            ];

            foreach ($employees as $employee) {
                foreach ($dates as $workDate) {
                    $workDate = $workDate->copy();

                    if (! $this->employeeExistsOnDate($employee, $workDate)) {
                        $summary['skipped']++;

                        continue;
                    }

                    $record = AttendanceRecord::query()
                        ->where('employee_id', $employee->id)
                        ->where('attendance_date', '>=', $workDate->toDateString())
                        ->where('attendance_date', '<', $this->nextDate($workDate->toDateString()))
                        ->first();

                    if (! $record && $workDate->equalTo($today)) {
                        $summary['skipped']++;

                        continue;
                    }

                    if (! $record) {
                        $record = AttendanceRecord::query()->create([
                            'employee_id' => $employee->id,
                            'attendance_date' => $workDate->toDateString(),
                            'created_by_user_id' => $actor->id,
                            'updated_by_user_id' => $actor->id,
                        ]);

                        $summary['created']++;
                    }

                    $before = Arr::only($record->getAttributes(), [
                        'primary_status',
                        'worked_minutes',
                        'late_minutes',
                        'overtime_minutes',
                        'is_weekend',
                        'is_holiday',
                        'holiday_name',
                    ]);

                    $record->setAttribute('attendance_date', $workDate->toDateString());
                    $record->updated_by_user_id = $actor->id;
                    $record->save();

                    $record = $this->calculateRecord($record);
                    $summary['processed']++;

                    $after = Arr::only($record->getAttributes(), [
                        'primary_status',
                        'worked_minutes',
                        'late_minutes',
                        'overtime_minutes',
                        'is_weekend',
                        'is_holiday',
                        'holiday_name',
                    ]);

                    if ($before !== $after) {
                        $summary['updated']++;
                    }
                }
            }

            return $summary;
        });
    }

    private function employeeExistsOnDate(Employee $employee, Carbon $date): bool
    {
        $timezone = $this->resolveEmployeeTimezone($employee);
        $joiningDate = $this->resolveCarbonValue($employee->date_of_joining, $timezone)?->startOfDay();

        if ($joiningDate && $date->lt($joiningDate)) {
            return false;
        }

        $terminatedAt = $this->resolveCarbonValue($employee->terminated_at, $timezone)?->startOfDay();

        return $terminatedAt === null || $date->lte($terminatedAt);
    }

    private function nextDate(string $date): string
    {
        return Carbon::parse($date)->addDay()->toDateString();
    }

    /**
     * @return list<int>
     */
    private function resolveNonWorkingDays(mixed $weekendRule): array
    {
        if (! is_array($weekendRule)) {
            return [];
        }

        $candidateDays = $weekendRule['non_working_days'] ?? [];

        if (! is_array($candidateDays)) {
            return [];
        }

        return array_values(array_map(
            static fn (mixed $day): int => (int) $day,
            $candidateDays,
        ));
    }

    private function resolveEmployeeTimezone(Employee $employee): string
    {
        $company = $employee->company;

        return $company instanceof Company
            ? $company->timezone
            : (string) config('app.timezone');
    }

    private function resolveUserTimezone(User $user): string
    {
        $company = $user->company;

        return $company instanceof Company
            ? $company->timezone
            : (string) config('app.timezone');
    }

    private function resolveCarbonValue(mixed $value, string $timezone): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        return Carbon::make($value)?->setTimezone($timezone);
    }
}
