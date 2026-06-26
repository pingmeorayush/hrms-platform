<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\EmploymentHistory;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type EmployeeCreatePayload array{
 *   employee_code?: string|null,
 *   first_name: string,
 *   middle_name?: string|null,
 *   last_name: string,
 *   email: string,
 *   phone?: string|null,
 *   date_of_birth?: string|null,
 *   gender?: string|null,
 *   marital_status?: string|null,
 *   date_of_joining: string,
 *   employment_type: string,
 *   employment_status?: string|null,
 *   department_id: int|string,
 *   designation_id: int|string,
 *   manager_id?: int|string|null,
 *   location_id?: int|string|null,
 *   cost_center_id?: int|string|null,
 *   user_id?: int|string|null
 * }
 * @phpstan-type EmployeeUpdatePayload array<string, mixed>
 * @phpstan-type EmployeeStructuralPayload array{
 *   effective_date: string,
 *   department_id?: int|string|null,
 *   designation_id?: int|string|null,
 *   manager_id?: int|string|null,
 *   location_id?: int|string|null,
 *   cost_center_id?: int|string|null,
 *   notes?: string|null
 * }
 * @phpstan-type EmployeeTerminationPayload array{
 *   termination_date: string,
 *   reason: string,
 *   notes?: string|null
 * }
 */
class EmployeeService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly EmployeeCodeService $employeeCodeService,
    ) {}

    /**
     * @param  EmployeeCreatePayload  $payload
     */
    public function create(User $actor, array $payload): Employee
    {
        return DB::transaction(function () use ($actor, $payload): Employee {
            $employeeCode = $this->employeeCodeService->resolveCodeForCreate(
                $actor->company_id,
                $payload['employee_code'] ?? null,
            );

            $employee = Employee::query()->create([
                'employee_code' => $employeeCode,
                'first_name' => $payload['first_name'],
                'middle_name' => $payload['middle_name'] ?? null,
                'last_name' => $payload['last_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'date_of_birth' => $payload['date_of_birth'] ?? null,
                'gender' => $payload['gender'] ?? null,
                'marital_status' => $payload['marital_status'] ?? null,
                'date_of_joining' => $payload['date_of_joining'],
                'employment_type' => $payload['employment_type'],
                'employment_status' => $payload['employment_status'] ?? 'active',
                'department_id' => $payload['department_id'],
                'designation_id' => $payload['designation_id'],
                'manager_id' => $payload['manager_id'] ?? null,
                'location_id' => $payload['location_id'] ?? null,
                'cost_center_id' => $payload['cost_center_id'] ?? null,
                'user_id' => $payload['user_id'] ?? null,
            ]);

            EmploymentHistory::query()->create([
                'employee_id' => $employee->id,
                'action' => 'created',
                'effective_date' => $employee->date_of_joining,
                'department_id' => $employee->department_id,
                'designation_id' => $employee->designation_id,
                'manager_id' => $employee->manager_id,
                'location_id' => $employee->location_id,
                'employment_status' => $employee->employment_status,
                'changed_by_user_id' => $actor->id,
                'metadata' => [
                    'employee_code' => $employee->employee_code,
                    'employment_type' => $employee->employment_type,
                ],
            ]);

            $this->auditLogger->record(
                eventType: 'employee.record.created',
                actor: $actor,
                metadata: [
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'email' => $employee->email,
                    'department_id' => $employee->department_id,
                    'designation_id' => $employee->designation_id,
                    'manager_id' => $employee->manager_id,
                ],
                entityType: 'employee',
                entityId: (string) $employee->id,
            );

            return $this->loadForResponse($employee);
        });
    }

    /**
     * @param  EmployeeUpdatePayload  $payload
     */
    public function update(Employee $employee, User $actor, array $payload): Employee
    {
        return DB::transaction(function () use ($employee, $actor, $payload): Employee {
            $this->ensureEmployeeIsMutable($employee, 'update');

            $trackedFields = [
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'phone',
                'date_of_birth',
                'gender',
                'marital_status',
                'employment_type',
                'user_id',
            ];

            $before = $employee->only($trackedFields);
            $changes = collect($trackedFields)
                ->filter(fn (string $field): bool => array_key_exists($field, $payload))
                ->mapWithKeys(fn (string $field): array => [$field => $payload[$field]])
                ->all();

            if ($changes === []) {
                throw ValidationException::withMessages([
                    'payload' => ['At least one editable employee field must be provided.'],
                ]);
            }

            $employee->fill($changes);

            if (! $employee->isDirty()) {
                throw ValidationException::withMessages([
                    'payload' => ['The submitted employee changes do not modify the current record.'],
                ]);
            }

            $employee->save();

            $after = $employee->only(array_keys($changes));

            $this->auditLogger->record(
                eventType: 'employee.record.updated',
                actor: $actor,
                metadata: [
                    'before' => Arr::only($before, array_keys($changes)),
                    'after' => $after,
                ],
                entityType: 'employee',
                entityId: (string) $employee->id,
            );

            return $this->loadForResponse($employee);
        });
    }

    /**
     * @param  EmployeeStructuralPayload  $payload
     */
    public function transfer(Employee $employee, User $actor, array $payload): Employee
    {
        return DB::transaction(function () use ($employee, $actor, $payload): Employee {
            $this->ensureEmployeeIsMutable($employee, 'transfer');

            $effectiveDate = $this->resolveEffectiveDate($employee, $payload['effective_date'], 'effective_date');
            $trackedFields = ['department_id', 'manager_id', 'location_id', 'cost_center_id'];
            $before = $employee->only($trackedFields);
            $changes = $this->resolveStructuralChanges($employee, $payload, $trackedFields);

            if ($changes === []) {
                throw ValidationException::withMessages([
                    'payload' => ['The submitted transfer does not change the employee structure.'],
                ]);
            }

            $employee->fill($changes);
            $employee->save();

            $this->recordEmploymentHistory(
                employee: $employee,
                actor: $actor,
                action: 'transferred',
                effectiveDate: $effectiveDate,
                before: $before,
                notes: $payload['notes'] ?? null,
                metadata: ['changed_fields' => array_keys($changes)],
            );

            $this->auditLogger->record(
                eventType: 'employee.record.transferred',
                actor: $actor,
                metadata: [
                    'effective_date' => $effectiveDate->toDateString(),
                    'before' => $before,
                    'after' => $employee->only($trackedFields),
                    'notes' => $payload['notes'] ?? null,
                ],
                entityType: 'employee',
                entityId: (string) $employee->id,
            );

            return $this->loadForResponse($employee);
        });
    }

    /**
     * @param  EmployeeStructuralPayload  $payload
     */
    public function promote(Employee $employee, User $actor, array $payload): Employee
    {
        return DB::transaction(function () use ($employee, $actor, $payload): Employee {
            $this->ensureEmployeeIsMutable($employee, 'promote');

            $effectiveDate = $this->resolveEffectiveDate($employee, $payload['effective_date'], 'effective_date');
            $trackedFields = ['designation_id', 'department_id', 'manager_id', 'location_id', 'cost_center_id'];
            $before = $employee->only($trackedFields);
            $changes = $this->resolveStructuralChanges($employee, $payload, $trackedFields);

            if ($changes === []) {
                throw ValidationException::withMessages([
                    'payload' => ['The submitted promotion does not change the employee assignment.'],
                ]);
            }

            $employee->fill($changes);
            $employee->save();

            $this->recordEmploymentHistory(
                employee: $employee,
                actor: $actor,
                action: 'promoted',
                effectiveDate: $effectiveDate,
                before: $before,
                notes: $payload['notes'] ?? null,
                metadata: ['changed_fields' => array_keys($changes)],
            );

            $this->auditLogger->record(
                eventType: 'employee.record.promoted',
                actor: $actor,
                metadata: [
                    'effective_date' => $effectiveDate->toDateString(),
                    'before' => $before,
                    'after' => $employee->only($trackedFields),
                    'notes' => $payload['notes'] ?? null,
                ],
                entityType: 'employee',
                entityId: (string) $employee->id,
            );

            return $this->loadForResponse($employee);
        });
    }

    /**
     * @param  EmployeeTerminationPayload  $payload
     */
    public function terminate(Employee $employee, User $actor, array $payload): Employee
    {
        return DB::transaction(function () use ($employee, $actor, $payload): Employee {
            if ($employee->employment_status === 'terminated' || $employee->terminated_at !== null) {
                throw ValidationException::withMessages([
                    'employee' => ['This employee has already been terminated.'],
                ]);
            }

            $terminationDate = $this->resolveEffectiveDate($employee, $payload['termination_date'], 'termination_date')->endOfDay();
            $before = $employee->only(['employment_status', 'termination_reason', 'terminated_at']);

            $employee->forceFill([
                'employment_status' => 'terminated',
                'termination_reason' => $payload['reason'],
                'terminated_at' => $terminationDate,
            ])->save();

            $employee->loadMissing('user');
            $userDeactivated = false;

            if ($employee->user) {
                $employee->user->forceFill(['is_active' => false])->save();
                $employee->user->tokens()->delete();
                $userDeactivated = true;
            }

            $this->recordEmploymentHistory(
                employee: $employee,
                actor: $actor,
                action: 'terminated',
                effectiveDate: $terminationDate,
                before: [],
                notes: $payload['notes'] ?? null,
                metadata: [
                    'termination_reason' => $payload['reason'],
                    'user_deactivated' => $userDeactivated,
                ],
                previousEmploymentStatus: $before['employment_status'],
                employmentStatus: 'terminated',
            );

            $this->auditLogger->record(
                eventType: 'employee.record.terminated',
                actor: $actor,
                metadata: [
                    'termination_date' => $terminationDate->toDateString(),
                    'reason' => $payload['reason'],
                    'before' => $before,
                    'after' => $employee->only(['employment_status', 'termination_reason', 'terminated_at']),
                    'user_deactivated' => $userDeactivated,
                ],
                entityType: 'employee',
                entityId: (string) $employee->id,
            );

            return $this->loadForResponse($employee);
        });
    }

    private function loadForResponse(Employee $employee): Employee
    {
        return $employee->fresh([
            'department',
            'designation',
            'manager',
            'location',
            'costCenter',
        ]);
    }

    private function ensureEmployeeIsMutable(Employee $employee, string $action): void
    {
        if ($employee->employment_status === 'terminated' || $employee->terminated_at !== null) {
            throw ValidationException::withMessages([
                'employee' => ["Terminated employees cannot be updated through the {$action} flow."],
            ]);
        }
    }

    private function resolveEffectiveDate(Employee $employee, string $value, string $field): Carbon
    {
        $effectiveDate = Carbon::parse($value)->startOfDay();
        $joiningDate = $employee->date_of_joining?->copy()->startOfDay();

        if ($joiningDate && $effectiveDate->lt($joiningDate)) {
            throw ValidationException::withMessages([
                $field => ['The effective date must be on or after the employee joining date.'],
            ]);
        }

        return $effectiveDate;
    }

    /**
     * @param  EmployeeUpdatePayload|EmployeeStructuralPayload  $payload
     * @param  list<string>  $trackedFields
     * @return array<string, mixed>
     */
    private function resolveStructuralChanges(Employee $employee, array $payload, array $trackedFields): array
    {
        $changes = [];

        foreach ($trackedFields as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            if ($employee->{$field} !== $payload[$field]) {
                $changes[$field] = $payload[$field];
            }
        }

        return $changes;
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $metadata
     */
    private function recordEmploymentHistory(
        Employee $employee,
        User $actor,
        string $action,
        Carbon $effectiveDate,
        array $before,
        ?string $notes = null,
        array $metadata = [],
        ?string $previousEmploymentStatus = null,
        ?string $employmentStatus = null,
    ): EmploymentHistory {
        return EmploymentHistory::query()->create([
            'employee_id' => $employee->id,
            'action' => $action,
            'effective_date' => $effectiveDate->toDateString(),
            'previous_department_id' => $before['department_id'] ?? $employee->department_id,
            'department_id' => $employee->department_id,
            'previous_designation_id' => $before['designation_id'] ?? $employee->designation_id,
            'designation_id' => $employee->designation_id,
            'previous_manager_id' => $before['manager_id'] ?? $employee->manager_id,
            'manager_id' => $employee->manager_id,
            'previous_location_id' => $before['location_id'] ?? $employee->location_id,
            'location_id' => $employee->location_id,
            'previous_employment_status' => $previousEmploymentStatus ?? ($before['employment_status'] ?? $employee->employment_status),
            'employment_status' => $employmentStatus ?? $employee->employment_status,
            'changed_by_user_id' => $actor->id,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }
}
