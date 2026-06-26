<?php

namespace App\Modules\AttendanceManagement\Services;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\HolidayCalendar;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftRoster;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AttendanceContextResolver
{
    /**
     * @return array{
     *   shift: Shift|null,
     *   shift_roster: ShiftRoster|null,
     *   schedule_source: string|null,
     *   scheduled_start_at: Carbon|null,
     *   scheduled_end_at: Carbon|null,
     *   scheduled_work_minutes: int|null,
     *   break_duration_minutes: int
     * }
     */
    public function resolveScheduleForDate(Employee $employee, string|Carbon $attendanceDate): array
    {
        $timezone = $this->resolveEmployeeTimezone($employee);
        $date = $attendanceDate instanceof Carbon
            ? $attendanceDate->copy()->setTimezone($timezone)->toDateString()
            : $attendanceDate;

        $roster = ShiftRoster::query()
            ->with('shift')
            ->where('employee_id', $employee->id)
            ->where('work_date', '>=', $date)
            ->where('work_date', '<', $this->nextDate($date))
            ->first();

        if ($roster) {
            return $this->buildSchedulePayload(
                $date,
                $timezone,
                $roster->shift,
                $roster,
                'roster',
            );
        }

        $scopes = [
            ['assignment_type' => 'employee', 'column' => 'employee_id', 'value' => $employee->id],
            ['assignment_type' => 'department', 'column' => 'department_id', 'value' => $employee->department_id],
            ['assignment_type' => 'location', 'column' => 'location_id', 'value' => $employee->location_id],
        ];

        foreach ($scopes as $scope) {
            if ($scope['value'] === null) {
                continue;
            }

            $assignment = ShiftAssignment::query()
                ->with('shift')
                ->where('assignment_type', $scope['assignment_type'])
                ->where($scope['column'], $scope['value'])
                ->where('status', 'active')
                ->where('effective_from', '<', $this->nextDate($date))
                ->where(function (Builder $query) use ($date): void {
                    $query
                        ->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $date);
                })
                ->orderByDesc('effective_from')
                ->first();

            if ($assignment) {
                return $this->buildSchedulePayload(
                    $date,
                    $timezone,
                    $assignment->shift,
                    null,
                    $scope['assignment_type'].'_assignment',
                );
            }
        }

        return $this->buildSchedulePayload($date, $timezone, null, null, null);
    }

    public function resolveHolidayForDate(Employee $employee, string|Carbon $attendanceDate): ?Holiday
    {
        $timezone = $this->resolveEmployeeTimezone($employee);
        $date = $attendanceDate instanceof Carbon
            ? $attendanceDate->copy()->setTimezone($timezone)->toDateString()
            : $attendanceDate;

        $calendars = HolidayCalendar::query()
            ->with(['holidays' => fn ($query) => $query
                ->where('holiday_date', '>=', $date)
                ->where('holiday_date', '<', $this->nextDate($date))])
            ->where('status', 'active')
            ->where(function (Builder $query) use ($employee): void {
                $query
                    ->where('is_default', true)
                    ->orWhere(function (Builder $calendarQuery) use ($employee): void {
                        $calendarQuery
                            ->where(function (Builder $locationQuery) use ($employee): void {
                                $locationQuery
                                    ->whereNull('location_id')
                                    ->orWhere('location_id', $employee->location_id);
                            })
                            ->where(function (Builder $departmentQuery) use ($employee): void {
                                $departmentQuery
                                    ->whereNull('department_id')
                                    ->orWhere('department_id', $employee->department_id);
                            });
                    });
            })
            ->get()
            ->filter(fn (HolidayCalendar $calendar): bool => $calendar->holidays->isNotEmpty())
            ->sortByDesc(fn (HolidayCalendar $calendar): int => $this->holidayCalendarSpecificity($calendar, $employee))
            ->values();

        return $calendars->first()?->holidays->first();
    }

    private function holidayCalendarSpecificity(HolidayCalendar $calendar, Employee $employee): int
    {
        if ($calendar->location_id !== null && $calendar->location_id === $employee->location_id
            && $calendar->department_id !== null && $calendar->department_id === $employee->department_id) {
            return 4;
        }

        if ($calendar->location_id !== null && $calendar->location_id === $employee->location_id) {
            return 3;
        }

        if ($calendar->department_id !== null && $calendar->department_id === $employee->department_id) {
            return 2;
        }

        if ($calendar->is_default) {
            return 1;
        }

        return 0;
    }

    /**
     * @return array{
     *   shift: Shift|null,
     *   shift_roster: ShiftRoster|null,
     *   schedule_source: string|null,
     *   scheduled_start_at: Carbon|null,
     *   scheduled_end_at: Carbon|null,
     *   scheduled_work_minutes: int|null,
     *   break_duration_minutes: int
     * }
     */
    private function buildSchedulePayload(string $attendanceDate, string $timezone, ?Shift $shift, ?ShiftRoster $roster, ?string $source): array
    {
        if (! $shift) {
            return [
                'shift' => null,
                'shift_roster' => $roster,
                'schedule_source' => $source,
                'scheduled_start_at' => null,
                'scheduled_end_at' => null,
                'scheduled_work_minutes' => null,
                'break_duration_minutes' => 0,
            ];
        }

        $scheduledStartAt = Carbon::parse($attendanceDate.' '.$shift->start_time, $timezone);
        $scheduledEndAt = Carbon::parse($attendanceDate.' '.$shift->end_time, $timezone);

        if ($shift->is_overnight || $scheduledEndAt->lte($scheduledStartAt)) {
            $scheduledEndAt = $scheduledEndAt->addDay();
        }

        return [
            'shift' => $shift,
            'shift_roster' => $roster,
            'schedule_source' => $source,
            'scheduled_start_at' => $scheduledStartAt,
            'scheduled_end_at' => $scheduledEndAt,
            'scheduled_work_minutes' => $shift->working_hours_minutes,
            'break_duration_minutes' => $shift->break_duration_minutes,
        ];
    }

    private function nextDate(string $date): string
    {
        return Carbon::parse($date)->addDay()->toDateString();
    }

    private function resolveEmployeeTimezone(Employee $employee): string
    {
        $company = $employee->company;

        return $company instanceof Company
            ? $company->timezone
            : (string) config('app.timezone');
    }
}
