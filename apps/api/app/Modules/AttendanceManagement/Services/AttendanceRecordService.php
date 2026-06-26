<?php

namespace App\Modules\AttendanceManagement\Services;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type AttendanceRecordFilters array{
 *   employee_id?: int|string,
 *   date_from?: string,
 *   date_to?: string,
 *   primary_status?: string,
 *   state?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type AttendanceCapturePayload array{
 *   captured_at?: string|null,
 *   channel?: string|null,
 *   device?: string|null,
 *   geolocation?: array<string, mixed>|string|null
 * }
 * @phpstan-type AttendanceCaptureContext array{
 *   ip_address?: string|null,
 *   user_agent?: string|null
 * }
 * @phpstan-type AttendanceCaptureMetadata array{
 *   device?: string,
 *   geolocation?: array<string, mixed>|string
 * }
 */
class AttendanceRecordService
{
    public function __construct(
        private readonly AttendanceAccessScopeService $attendanceAccessScopeService,
        private readonly AttendanceCalculationService $attendanceCalculationService,
        private readonly AttendanceContextResolver $attendanceContextResolver,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  AttendanceRecordFilters  $filters
     * @return LengthAwarePaginator<int, AttendanceRecord>
     */
    public function search(User $actor, array $filters): LengthAwarePaginator
    {
        $query = $this->attendanceAccessScopeService
            ->attendanceRecordsQuery($actor)
            ->when(
                array_key_exists('employee_id', $filters),
                fn (Builder $builder) => $builder->where('employee_id', $filters['employee_id']),
            )
            ->when(
                array_key_exists('date_from', $filters),
                fn (Builder $builder) => $builder->where('attendance_date', '>=', $filters['date_from']),
            )
            ->when(
                array_key_exists('date_to', $filters),
                fn (Builder $builder) => $builder->where('attendance_date', '<', $this->nextDate((string) $filters['date_to'])),
            )
            ->when(
                array_key_exists('primary_status', $filters),
                fn (Builder $builder) => $builder->where('primary_status', $filters['primary_status']),
            )
            ->when(
                ($filters['state'] ?? null) === 'checked_in',
                fn (Builder $builder) => $builder->whereNotNull('check_in_at')->whereNull('check_out_at'),
            )
            ->when(
                ($filters['state'] ?? null) === 'checked_out',
                fn (Builder $builder) => $builder->whereNotNull('check_in_at')->whereNotNull('check_out_at'),
            )
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_at');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function findForView(User $actor, int $attendanceRecordId): AttendanceRecord
    {
        return $this->attendanceAccessScopeService
            ->attendanceRecordsQuery($actor)
            ->findOrFail($attendanceRecordId);
    }

    /**
     * @param  AttendanceCapturePayload  $payload
     * @param  AttendanceCaptureContext  $context
     */
    public function checkIn(User $actor, array $payload, array $context): AttendanceRecord
    {
        return DB::transaction(function () use ($actor, $payload, $context): AttendanceRecord {
            $employee = $this->resolveLinkedEmployee($actor);
            $capturedAt = $this->resolveCapturedAt($actor->company, $payload['captured_at'] ?? null);
            $attendanceDate = $capturedAt->copy()->setTimezone($actor->company->timezone)->toDateString();

            $this->ensureEmployeeCanCaptureAttendance($employee);
            $this->ensureCheckInIsAllowed($employee, $attendanceDate);

            $schedule = $this->attendanceContextResolver->resolveScheduleForDate($employee, $attendanceDate);

            $record = AttendanceRecord::query()->create([
                'employee_id' => $employee->id,
                'shift_id' => $schedule['shift']?->id,
                'shift_roster_id' => $schedule['shift_roster']?->id,
                'attendance_date' => $attendanceDate,
                'check_in_at' => $capturedAt,
                'check_in_channel' => $payload['channel'] ?? 'api',
                'check_in_ip_address' => $context['ip_address'] ?? null,
                'check_in_user_agent' => $context['user_agent'] ?? null,
                'check_in_metadata' => $this->extractCaptureMetadata($payload),
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'attendance.record.checked_in',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'attendance_record_id' => $record->id,
                    'attendance_date' => $attendanceDate,
                    'shift_id' => $schedule['shift']?->id,
                    'shift_roster_id' => $schedule['shift_roster']?->id,
                    'check_in_at' => $capturedAt->toIso8601String(),
                    'channel' => $record->check_in_channel,
                    'metadata' => $record->check_in_metadata ?? [],
                ],
                ipAddress: $record->check_in_ip_address,
                userAgent: $record->check_in_user_agent,
                entityType: 'attendance_record',
                entityId: (string) $record->id,
            );

            return $this->attendanceCalculationService->calculateRecord($record);
        });
    }

    /**
     * @param  AttendanceCapturePayload  $payload
     * @param  AttendanceCaptureContext  $context
     */
    public function checkOut(User $actor, array $payload, array $context): AttendanceRecord
    {
        return DB::transaction(function () use ($actor, $payload, $context): AttendanceRecord {
            $employee = $this->resolveLinkedEmployee($actor);
            $capturedAt = $this->resolveCapturedAt($actor->company, $payload['captured_at'] ?? null);

            $this->ensureEmployeeCanCaptureAttendance($employee);

            $record = AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->whereNull('check_out_at')
                ->orderByDesc('check_in_at')
                ->first();

            if (! $record) {
                throw ValidationException::withMessages([
                    'attendance' => ['Check-out is not allowed without an active check-in.'],
                ]);
            }

            if ($capturedAt->lte($record->check_in_at)) {
                throw ValidationException::withMessages([
                    'captured_at' => ['Check-out time must be after the recorded check-in time.'],
                ]);
            }

            $workedMinutes = $record->check_in_at->diffInMinutes($capturedAt);

            $record->forceFill([
                'check_out_at' => $capturedAt,
                'check_out_channel' => $payload['channel'] ?? 'api',
                'check_out_ip_address' => $context['ip_address'] ?? null,
                'check_out_user_agent' => $context['user_agent'] ?? null,
                'check_out_metadata' => $this->extractCaptureMetadata($payload),
                'worked_minutes' => $workedMinutes,
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'attendance.record.checked_out',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'attendance_record_id' => $record->id,
                    'attendance_date' => $record->attendance_date?->toDateString(),
                    'check_out_at' => $capturedAt->toIso8601String(),
                    'worked_minutes' => $workedMinutes,
                    'channel' => $record->check_out_channel,
                    'metadata' => $record->check_out_metadata ?? [],
                ],
                ipAddress: $record->check_out_ip_address,
                userAgent: $record->check_out_user_agent,
                entityType: 'attendance_record',
                entityId: (string) $record->id,
            );

            return $this->attendanceCalculationService->calculateRecord($record);
        });
    }

    private function resolveLinkedEmployee(User $actor): Employee
    {
        $employee = $this->attendanceAccessScopeService->findLinkedEmployee($actor);

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => ['No employee profile is linked to the authenticated user.'],
            ]);
        }

        return $employee;
    }

    private function ensureEmployeeCanCaptureAttendance(Employee $employee): void
    {
        if ($employee->terminated_at !== null || $employee->employment_status === 'terminated') {
            throw ValidationException::withMessages([
                'employee' => ['Terminated employees cannot record attendance.'],
            ]);
        }
    }

    private function ensureCheckInIsAllowed(Employee $employee, string $attendanceDate): void
    {
        $hasOpenRecord = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereNull('check_out_at')
            ->exists();

        if ($hasOpenRecord) {
            throw ValidationException::withMessages([
                'attendance' => ['A prior check-in is still open and must be checked out or corrected first.'],
            ]);
        }

        $hasExistingAttendance = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->where('attendance_date', '>=', $attendanceDate)
            ->where('attendance_date', '<', $this->nextDate($attendanceDate))
            ->exists();

        if ($hasExistingAttendance) {
            throw ValidationException::withMessages([
                'attendance' => ['Attendance has already been recorded for this working day.'],
            ]);
        }
    }

    private function resolveCapturedAt(Company $company, ?string $value): Carbon
    {
        $timestamp = $value !== null
            ? $this->parseCapturedAt($company, $value)
            : now($company->timezone);

        return $timestamp->copy()->setTimezone($company->timezone);
    }

    private function parseCapturedAt(Company $company, string $value): Carbon
    {
        if (preg_match('/(?:Z|[+\-]\d{2}:\d{2})$/', $value) === 1) {
            return Carbon::parse($value);
        }

        return Carbon::parse($value, $company->timezone);
    }

    /**
     * @param  AttendanceCapturePayload  $payload
     * @return AttendanceCaptureMetadata
     */
    private function extractCaptureMetadata(array $payload): array
    {
        return array_filter([
            'device' => $payload['device'] ?? null,
            'geolocation' => $payload['geolocation'] ?? null,
        ], fn (mixed $value): bool => $value !== null);
    }

    private function nextDate(string $date): string
    {
        return Carbon::parse($date)->addDay()->toDateString();
    }
}
