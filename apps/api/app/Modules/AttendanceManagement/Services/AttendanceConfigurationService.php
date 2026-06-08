<?php

namespace App\Modules\AttendanceManagement\Services;

use App\Models\AttendancePolicy;
use App\Models\Holiday;
use App\Models\HolidayCalendar;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class AttendanceConfigurationService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function getOrCreatePolicy(): AttendancePolicy
    {
        return AttendancePolicy::query()->firstOrCreate(
            [],
            [
                'name' => 'Default Attendance Policy',
                'working_hours_minutes' => 480,
                'grace_minutes' => 15,
                'late_after_minutes' => 15,
                'half_day_minutes' => 240,
                'overtime_eligible' => true,
                'overtime_after_minutes' => 480,
                'weekend_rule' => ['non_working_days' => [0, 6]],
                'work_from_home_allowed' => false,
                'enforce_geofence' => false,
                'allowed_radius_meters' => null,
                'status' => 'active',
            ],
        );
    }

    public function updatePolicy(User $actor, AttendancePolicy $policy, array $payload): AttendancePolicy
    {
        return DB::transaction(function () use ($actor, $policy, $payload): AttendancePolicy {
            $before = $policy->only([
                'name',
                'working_hours_minutes',
                'grace_minutes',
                'late_after_minutes',
                'half_day_minutes',
                'overtime_eligible',
                'overtime_after_minutes',
                'weekend_rule',
                'work_from_home_allowed',
                'enforce_geofence',
                'allowed_radius_meters',
                'status',
            ]);

            $policy->fill($this->normalizePolicyPayload($payload));
            $policy->save();

            $this->auditLogger->record(
                eventType: 'attendance.policy.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $policy->only([
                        'name',
                        'working_hours_minutes',
                        'grace_minutes',
                        'late_after_minutes',
                        'half_day_minutes',
                        'overtime_eligible',
                        'overtime_after_minutes',
                        'weekend_rule',
                        'work_from_home_allowed',
                        'enforce_geofence',
                        'allowed_radius_meters',
                        'status',
                    ]),
                ],
                entityType: 'attendance_policy',
                entityId: (string) $policy->id,
            );

            return $policy->refresh();
        });
    }

    public function createHolidayCalendar(User $actor, array $payload): HolidayCalendar
    {
        return DB::transaction(function () use ($actor, $payload): HolidayCalendar {
            $payload = $this->normalizeHolidayCalendarPayload($payload);

            if ($payload['is_default']) {
                HolidayCalendar::query()->update(['is_default' => false]);
            }

            $holidayCalendar = HolidayCalendar::query()->create($payload);

            $this->auditLogger->record(
                eventType: 'attendance.holiday_calendar.created',
                actor: $actor,
                metadata: $holidayCalendar->only([
                    'code',
                    'name',
                    'location_id',
                    'department_id',
                    'is_default',
                    'status',
                ]),
                entityType: 'holiday_calendar',
                entityId: (string) $holidayCalendar->id,
            );

            return $holidayCalendar->load(['location', 'department', 'holidays']);
        });
    }

    public function updateHolidayCalendar(User $actor, HolidayCalendar $holidayCalendar, array $payload): HolidayCalendar
    {
        return DB::transaction(function () use ($actor, $holidayCalendar, $payload): HolidayCalendar {
            $payload = $this->normalizeHolidayCalendarPayload($payload);
            $before = $holidayCalendar->only([
                'code',
                'name',
                'description',
                'location_id',
                'department_id',
                'is_default',
                'status',
            ]);

            if ($payload['is_default']) {
                HolidayCalendar::query()
                    ->whereKeyNot($holidayCalendar->id)
                    ->update(['is_default' => false]);
            }

            $holidayCalendar->fill($payload);
            $holidayCalendar->save();

            $this->auditLogger->record(
                eventType: 'attendance.holiday_calendar.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $holidayCalendar->only([
                        'code',
                        'name',
                        'description',
                        'location_id',
                        'department_id',
                        'is_default',
                        'status',
                    ]),
                ],
                entityType: 'holiday_calendar',
                entityId: (string) $holidayCalendar->id,
            );

            return $holidayCalendar->refresh()->load(['location', 'department', 'holidays']);
        });
    }

    public function createHoliday(User $actor, HolidayCalendar $holidayCalendar, array $payload): Holiday
    {
        return DB::transaction(function () use ($actor, $holidayCalendar, $payload): Holiday {
            $payload['holiday_calendar_id'] = $holidayCalendar->id;
            $payload['is_optional'] = ($payload['type'] ?? null) === 'optional' ? true : (bool) $payload['is_optional'];

            $holiday = Holiday::query()->create($payload);

            $this->auditLogger->record(
                eventType: 'attendance.holiday.created',
                actor: $actor,
                metadata: $holiday->only(['holiday_calendar_id', 'name', 'holiday_date', 'type', 'is_optional']),
                entityType: 'holiday',
                entityId: (string) $holiday->id,
            );

            return $holiday->refresh();
        });
    }

    public function updateHoliday(User $actor, HolidayCalendar $holidayCalendar, Holiday $holiday, array $payload): Holiday
    {
        return DB::transaction(function () use ($actor, $holidayCalendar, $holiday, $payload): Holiday {
            $before = $holiday->only(['holiday_calendar_id', 'name', 'holiday_date', 'type', 'is_optional', 'description']);
            $payload['holiday_calendar_id'] = $holidayCalendar->id;
            $payload['is_optional'] = ($payload['type'] ?? null) === 'optional' ? true : (bool) $payload['is_optional'];

            $holiday->fill($payload);
            $holiday->save();

            $this->auditLogger->record(
                eventType: 'attendance.holiday.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $holiday->only([
                        'holiday_calendar_id',
                        'name',
                        'holiday_date',
                        'type',
                        'is_optional',
                        'description',
                    ]),
                ],
                entityType: 'holiday',
                entityId: (string) $holiday->id,
            );

            return $holiday->refresh();
        });
    }

    private function normalizePolicyPayload(array $payload): array
    {
        $payload['weekend_rule'] = [
            'non_working_days' => collect($payload['weekend_rule']['non_working_days'] ?? [])
                ->map(fn (mixed $day): int => (int) $day)
                ->unique()
                ->sort()
                ->values()
                ->all(),
        ];

        if (! $payload['overtime_eligible']) {
            $payload['overtime_after_minutes'] = null;
        }

        if (! $payload['enforce_geofence']) {
            $payload['allowed_radius_meters'] = null;
        }

        return $payload;
    }

    private function normalizeHolidayCalendarPayload(array $payload): array
    {
        if ($payload['is_default']) {
            $payload['location_id'] = null;
            $payload['department_id'] = null;
        }

        return $payload;
    }
}
