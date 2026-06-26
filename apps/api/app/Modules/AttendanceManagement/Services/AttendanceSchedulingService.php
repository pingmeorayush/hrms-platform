<?php

namespace App\Modules\AttendanceManagement\Services;

use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftRoster;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type ShiftPayload array<string, mixed>
 * @phpstan-type ShiftAssignmentPayload array<string, mixed>
 * @phpstan-type ShiftRosterEntry array{
 *   employee_id: int|string,
 *   shift_id: int|string,
 *   work_date: string,
 *   notes?: string|null,
 *   status?: string|null
 * }
 * @phpstan-type ShiftRosterBatchPayload array{entries: list<ShiftRosterEntry>}
 * @phpstan-type ShiftRosterPayload array<string, mixed>
 */
class AttendanceSchedulingService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  ShiftPayload  $payload
     */
    public function createShift(User $actor, array $payload): Shift
    {
        return DB::transaction(function () use ($actor, $payload): Shift {
            $payload = $this->normalizeShiftPayload($payload);

            $shift = Shift::query()->create($payload);

            $this->auditLogger->record(
                eventType: 'attendance.shift.created',
                actor: $actor,
                metadata: $shift->only([
                    'code',
                    'name',
                    'start_time',
                    'end_time',
                    'working_hours_minutes',
                    'is_overnight',
                    'status',
                ]),
                entityType: 'shift',
                entityId: (string) $shift->id,
            );

            return $shift->refresh();
        });
    }

    /**
     * @param  ShiftPayload  $payload
     */
    public function updateShift(User $actor, Shift $shift, array $payload): Shift
    {
        return DB::transaction(function () use ($actor, $shift, $payload): Shift {
            $before = $shift->only([
                'code',
                'name',
                'description',
                'start_time',
                'end_time',
                'break_duration_minutes',
                'grace_minutes',
                'working_hours_minutes',
                'is_overnight',
                'status',
            ]);

            $shift->fill($this->normalizeShiftPayload($payload));
            $shift->save();

            $this->auditLogger->record(
                eventType: 'attendance.shift.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $shift->only([
                        'code',
                        'name',
                        'description',
                        'start_time',
                        'end_time',
                        'break_duration_minutes',
                        'grace_minutes',
                        'working_hours_minutes',
                        'is_overnight',
                        'status',
                    ]),
                ],
                entityType: 'shift',
                entityId: (string) $shift->id,
            );

            return $shift->refresh();
        });
    }

    /**
     * @param  ShiftAssignmentPayload  $payload
     */
    public function createShiftAssignment(User $actor, array $payload): ShiftAssignment
    {
        return DB::transaction(function () use ($actor, $payload): ShiftAssignment {
            $payload['created_by_user_id'] = $actor->id;
            $this->ensureAssignmentDoesNotOverlap($payload);

            $assignment = ShiftAssignment::query()->create($payload);

            $this->auditLogger->record(
                eventType: 'attendance.shift_assignment.created',
                actor: $actor,
                metadata: $assignment->only([
                    'shift_id',
                    'assignment_type',
                    'employee_id',
                    'department_id',
                    'location_id',
                    'effective_from',
                    'effective_to',
                    'status',
                ]),
                entityType: 'shift_assignment',
                entityId: (string) $assignment->id,
            );

            return $assignment->load(['shift', 'employee', 'department', 'location']);
        });
    }

    /**
     * @param  ShiftAssignmentPayload  $payload
     */
    public function updateShiftAssignment(User $actor, ShiftAssignment $assignment, array $payload): ShiftAssignment
    {
        return DB::transaction(function () use ($actor, $assignment, $payload): ShiftAssignment {
            $before = $assignment->only([
                'shift_id',
                'assignment_type',
                'employee_id',
                'department_id',
                'location_id',
                'effective_from',
                'effective_to',
                'notes',
                'status',
            ]);

            $candidate = array_merge($before, $payload);
            $this->ensureAssignmentDoesNotOverlap($candidate, $assignment->id);

            $assignment->fill($payload);
            $assignment->save();

            $this->auditLogger->record(
                eventType: 'attendance.shift_assignment.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $assignment->only([
                        'shift_id',
                        'assignment_type',
                        'employee_id',
                        'department_id',
                        'location_id',
                        'effective_from',
                        'effective_to',
                        'notes',
                        'status',
                    ]),
                ],
                entityType: 'shift_assignment',
                entityId: (string) $assignment->id,
            );

            return $assignment->refresh()->load(['shift', 'employee', 'department', 'location']);
        });
    }

    /**
     * @param  ShiftRosterBatchPayload  $payload
     * @return Collection<int, ShiftRoster>
     */
    public function createRosters(User $actor, array $payload): Collection
    {
        return DB::transaction(function () use ($actor, $payload): Collection {
            $entries = $this->normalizeRosterEntries($payload);
            $this->ensureRosterEntriesDoNotConflict($entries);

            $created = collect($entries)->map(function (array $entry) use ($actor): ShiftRoster {
                $roster = ShiftRoster::query()->create([
                    'employee_id' => $entry['employee_id'],
                    'shift_id' => $entry['shift_id'],
                    'work_date' => $entry['work_date'],
                    'notes' => $entry['notes'] ?? null,
                    'status' => $entry['status'] ?? 'scheduled',
                    'created_by_user_id' => $actor->id,
                ]);

                return $roster->load(['employee', 'shift']);
            });

            $this->auditLogger->record(
                eventType: 'attendance.roster.scheduled',
                actor: $actor,
                metadata: [
                    'count' => $created->count(),
                    'entries' => $created->map(fn (ShiftRoster $roster): array => [
                        'employee_id' => $roster->employee_id,
                        'shift_id' => $roster->shift_id,
                        'work_date' => $roster->work_date?->toDateString(),
                        'status' => $roster->status,
                    ])->all(),
                ],
                entityType: 'shift_roster_batch',
                entityId: null,
            );

            return $created;
        });
    }

    /**
     * @param  ShiftRosterPayload  $payload
     */
    public function updateRoster(User $actor, ShiftRoster $roster, array $payload): ShiftRoster
    {
        return DB::transaction(function () use ($actor, $roster, $payload): ShiftRoster {
            $before = $roster->only(['shift_id', 'work_date', 'notes', 'status']);
            $candidate = array_merge($before, $payload, ['employee_id' => $roster->employee_id]);
            $this->ensureSingleRosterDoesNotConflict($candidate, $roster->id);

            $roster->fill($payload);
            $roster->save();

            $this->auditLogger->record(
                eventType: 'attendance.roster.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $roster->only(['shift_id', 'work_date', 'notes', 'status']),
                ],
                entityType: 'shift_roster',
                entityId: (string) $roster->id,
            );

            return $roster->refresh()->load(['employee', 'shift']);
        });
    }

    /**
     * @param  ShiftPayload  $payload
     * @return ShiftPayload
     */
    private function normalizeShiftPayload(array $payload): array
    {
        $payload['is_overnight'] = $payload['end_time'] < $payload['start_time'];

        return $payload;
    }

    /**
     * @param  ShiftRosterBatchPayload  $payload
     * @return list<ShiftRosterEntry>
     */
    private function normalizeRosterEntries(array $payload): array
    {
        return $payload['entries'];
    }

    /**
     * @param  ShiftAssignmentPayload  $payload
     */
    private function ensureAssignmentDoesNotOverlap(array $payload, ?int $ignoreId = null): void
    {
        if (($payload['status'] ?? 'active') !== 'active') {
            return;
        }

        $scopeColumn = $this->scopeColumn((string) $payload['assignment_type']);
        $scopeId = $payload[$scopeColumn];
        $effectiveFrom = (string) $payload['effective_from'];
        $effectiveTo = $payload['effective_to'] ?? null;

        $overlapExists = ShiftAssignment::query()
            ->where('assignment_type', $payload['assignment_type'])
            ->where($scopeColumn, $scopeId)
            ->where('status', 'active')
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where(
                'effective_from',
                '<',
                $effectiveTo !== null ? $this->nextDate((string) $effectiveTo) : '9999-12-31 23:59:59',
            )
            ->where(function (Builder $query) use ($effectiveFrom): void {
                $query
                    ->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $effectiveFrom);
            })
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                $scopeColumn => ['An overlapping active shift assignment already exists for this scope.'],
            ]);
        }
    }

    /**
     * @param  list<ShiftRosterEntry>  $entries
     */
    private function ensureRosterEntriesDoNotConflict(array $entries): void
    {
        $existingConflicts = collect($entries)->first(function (array $entry): bool {
            return ShiftRoster::query()
                ->where('employee_id', $entry['employee_id'])
                ->where('work_date', '>=', $entry['work_date'])
                ->where('work_date', '<', $this->nextDate((string) $entry['work_date']))
                ->exists();
        });

        if ($existingConflicts !== null) {
            throw ValidationException::withMessages([
                'entries' => ['A roster already exists for one or more employee and work-date combinations in this request.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function ensureSingleRosterDoesNotConflict(array $payload, ?int $ignoreId = null): void
    {
        $conflictExists = ShiftRoster::query()
            ->where('employee_id', $payload['employee_id'])
            ->where('work_date', '>=', $payload['work_date'])
            ->where('work_date', '<', $this->nextDate((string) $payload['work_date']))
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($conflictExists) {
            throw ValidationException::withMessages([
                'work_date' => ['A roster already exists for this employee and work date.'],
            ]);
        }
    }

    private function scopeColumn(string $assignmentType): string
    {
        return match ($assignmentType) {
            'employee' => 'employee_id',
            'department' => 'department_id',
            'location' => 'location_id',
            default => throw ValidationException::withMessages([
                'assignment_type' => ['Unsupported assignment type.'],
            ]),
        };
    }

    private function nextDate(string $date): string
    {
        return Carbon::parse($date)->addDay()->toDateString();
    }
}
