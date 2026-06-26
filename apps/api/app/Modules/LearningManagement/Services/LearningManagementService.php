<?php

namespace App\Modules\LearningManagement\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LearningAssignment;
use App\Models\LearningAssignmentTarget;
use App\Models\LearningItem;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type LearningItemFilters array{
 *   category?: string,
 *   status?: string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type LearningAssignmentFilters array{
 *   learning_item_id?: int|string,
 *   audience_type?: string,
 *   status?: string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type LearningTargetFilters array{
 *   learning_assignment_id?: int|string,
 *   learning_item_id?: int|string,
 *   employee_id?: int|string,
 *   status?: string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type LearningCompletionEvidence array{
 *   type: string,
 *   reference: string,
 *   notes?: string|null
 * }
 * @phpstan-type LearningItemPayload array{
 *   code: string,
 *   title: string,
 *   description?: string|null,
 *   category: string,
 *   delivery_mode: string,
 *   duration_minutes?: int|string|null,
 *   requires_completion_evidence?: bool|int|string,
 *   renewal_frequency_months?: int|string|null,
 *   default_due_days?: int|string|null,
 *   metadata?: array<string, mixed>|null,
 *   status: string
 * }
 * @phpstan-type LearningItemUpdatePayload array{
 *   code?: string,
 *   title?: string,
 *   description?: string|null,
 *   category?: string,
 *   delivery_mode?: string,
 *   duration_minutes?: int|string|null,
 *   requires_completion_evidence?: bool|int|string,
 *   renewal_frequency_months?: int|string|null,
 *   default_due_days?: int|string|null,
 *   metadata?: array<string, mixed>|null,
 *   status?: string
 * }
 * @phpstan-type LearningAudienceRules array{
 *   employee_ids?: list<int|string>,
 *   department_ids?: list<int|string>,
 *   designation_ids?: list<int|string>
 * }
 * @phpstan-type LearningAssignmentPayload array{
 *   learning_item_id: int|string,
 *   audience_type: string,
 *   audience_rules: LearningAudienceRules,
 *   assigned_on?: string|null,
 *   due_on?: string|null,
 *   requires_completion_evidence?: bool|int|string,
 *   renewal_frequency_months?: int|string|null,
 *   default_due_days?: int|string|null,
 *   notes?: string|null
 * }
 * @phpstan-type LearningCompleteTargetPayload array{
 *   completion_notes?: string|null,
 *   completion_evidence?: LearningCompletionEvidence|null
 * }
 */
class LearningManagementService
{
    public function __construct(
        private readonly LearningAccessScopeService $accessScopeService,
        private readonly LearningTrackingStateResolver $trackingStateResolver,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  LearningItemFilters  $filters
     * @return LengthAwarePaginator<int, LearningItem>
     */
    public function searchItems(User $actor, array $filters): LengthAwarePaginator
    {
        $results = $this->accessScopeService
            ->itemsQuery($actor)
            ->when(array_key_exists('category', $filters), fn (Builder $builder) => $builder->where('category', $filters['category']))
            ->when(array_key_exists('status', $filters), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(
                array_key_exists('q', $filters),
                function (Builder $builder) use ($filters): void {
                    $term = trim((string) $filters['q']);

                    $builder->where(function (Builder $query) use ($term): void {
                        $query->where('code', 'like', '%'.$term.'%')
                            ->orWhere('title', 'like', '%'.$term.'%')
                            ->orWhere('description', 'like', '%'.$term.'%');
                    });
                },
            )
            ->orderBy('title')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15));

        $this->auditLogger->record(
            eventType: 'learning.item.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'item_count' => $results->total(),
            ],
            entityType: 'learning_item',
        );

        return $results;
    }

    /**
     * @param  LearningAssignmentFilters  $filters
     * @return LengthAwarePaginator<int, LearningAssignment>
     */
    public function searchAssignments(User $actor, array $filters): LengthAwarePaginator
    {
        $results = $this->accessScopeService
            ->assignmentsQuery($actor)
            ->when(
                array_key_exists('learning_item_id', $filters),
                fn (Builder $builder) => $builder->where('learning_item_id', $filters['learning_item_id']),
            )
            ->when(
                array_key_exists('audience_type', $filters),
                fn (Builder $builder) => $builder->where('audience_type', $filters['audience_type']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('q', $filters),
                function (Builder $builder) use ($filters): void {
                    $term = trim((string) $filters['q']);

                    $builder->where(function (Builder $query) use ($term): void {
                        $query->where('assignment_code', 'like', '%'.$term.'%')
                            ->orWhereHas('item', function (Builder $itemQuery) use ($term): void {
                                $itemQuery->where('code', 'like', '%'.$term.'%')
                                    ->orWhere('title', 'like', '%'.$term.'%');
                            });
                    });
                },
            )
            ->orderByDesc('assigned_on')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15));

        $this->auditLogger->record(
            eventType: 'learning.assignment.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'assignment_count' => $results->total(),
            ],
            entityType: 'learning_assignment',
        );

        return $results;
    }

    /**
     * @param  LearningTargetFilters  $filters
     * @return LengthAwarePaginator<int, LearningAssignmentTarget>
     */
    public function searchTargets(User $actor, array $filters): LengthAwarePaginator
    {
        $results = $this->buildTargetsQuery($this->accessScopeService->targetsQuery($actor), $filters)
            ->paginate((int) ($filters['per_page'] ?? 15));

        $this->auditLogger->record(
            eventType: 'learning.target.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'target_count' => $results->total(),
            ],
            entityType: 'learning_assignment_target',
        );

        return $results;
    }

    /**
     * @param  LearningTargetFilters  $filters
     * @return LengthAwarePaginator<int, LearningAssignmentTarget>
     */
    public function searchMyTargets(User $actor, array $filters): LengthAwarePaginator
    {
        $results = $this->buildTargetsQuery($this->accessScopeService->myTargetsQuery($actor), $filters)
            ->paginate((int) ($filters['per_page'] ?? 15));

        $this->auditLogger->record(
            eventType: 'learning.target.my_assignments_viewed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'target_count' => $results->total(),
            ],
            entityType: 'learning_assignment_target',
        );

        return $results;
    }

    public function findItemForView(User $actor, int $learningItemId): LearningItem
    {
        $item = $this->accessScopeService->resolveAccessibleItem($actor, $learningItemId);

        $this->auditLogger->record(
            eventType: 'learning.item.viewed',
            actor: $actor,
            metadata: [
                'learning_item_id' => $item->id,
                'code' => $item->code,
                'status' => $item->status,
            ],
            entityType: 'learning_item',
            entityId: (string) $item->id,
        );

        return $item;
    }

    public function findAssignmentForView(User $actor, int $learningAssignmentId): LearningAssignment
    {
        $assignment = $this->accessScopeService->resolveAccessibleAssignment($actor, $learningAssignmentId);

        $this->auditLogger->record(
            eventType: 'learning.assignment.viewed',
            actor: $actor,
            metadata: [
                'learning_assignment_id' => $assignment->id,
                'assignment_code' => $assignment->assignment_code,
                'target_count' => $assignment->target_count,
            ],
            entityType: 'learning_assignment',
            entityId: (string) $assignment->id,
        );

        return $assignment;
    }

    public function findTargetForView(User $actor, int $learningAssignmentTargetId): LearningAssignmentTarget
    {
        $target = $this->accessScopeService->resolveAccessibleTarget($actor, $learningAssignmentTargetId);

        $this->auditLogger->record(
            eventType: 'learning.target.viewed',
            actor: $actor,
            metadata: [
                'learning_assignment_target_id' => $target->id,
                'learning_assignment_id' => $target->learning_assignment_id,
                'employee_id' => $target->employee_id,
                'status' => $target->status,
            ],
            entityType: 'learning_assignment_target',
            entityId: (string) $target->id,
        );

        return $target;
    }

    /**
     * @param  LearningItemPayload  $payload
     */
    public function createItem(User $actor, array $payload): LearningItem
    {
        return DB::transaction(function () use ($actor, $payload): LearningItem {
            $this->ensureLearningItemCodeUnique($actor->company_id, $payload['code']);

            $item = LearningItem::query()->create([
                'company_id' => $actor->company_id,
                'code' => strtoupper(trim((string) $payload['code'])),
                'title' => trim((string) $payload['title']),
                'description' => $payload['description'] ?? null,
                'category' => $payload['category'],
                'delivery_mode' => $payload['delivery_mode'],
                'duration_minutes' => $payload['duration_minutes'] ?? null,
                'requires_completion_evidence' => $payload['requires_completion_evidence'] ?? false,
                'renewal_frequency_months' => $payload['renewal_frequency_months'] ?? null,
                'default_due_days' => $payload['default_due_days'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
                'status' => $payload['status'],
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'learning.item.created',
                actor: $actor,
                metadata: $item->only([
                    'code',
                    'title',
                    'category',
                    'delivery_mode',
                    'requires_completion_evidence',
                    'renewal_frequency_months',
                    'default_due_days',
                    'status',
                ]),
                entityType: 'learning_item',
                entityId: (string) $item->id,
            );

            return $this->accessScopeService->resolveAccessibleItem($actor, $item->id);
        });
    }

    /**
     * @param  LearningItemUpdatePayload  $payload
     */
    public function updateItem(User $actor, LearningItem $item, array $payload): LearningItem
    {
        return DB::transaction(function () use ($actor, $item, $payload): LearningItem {
            $before = $item->only([
                'code',
                'title',
                'description',
                'category',
                'delivery_mode',
                'duration_minutes',
                'requires_completion_evidence',
                'renewal_frequency_months',
                'default_due_days',
                'metadata',
                'status',
            ]);

            $merged = array_merge($item->toArray(), $payload);
            $code = strtoupper(trim((string) $merged['code']));
            $this->ensureLearningItemCodeUnique($actor->company_id, $code, $item->id);

            $item->fill([
                'code' => $code,
                'title' => trim((string) $merged['title']),
                'description' => $merged['description'] ?? null,
                'category' => $merged['category'],
                'delivery_mode' => $merged['delivery_mode'],
                'duration_minutes' => $merged['duration_minutes'] ?? null,
                'requires_completion_evidence' => $merged['requires_completion_evidence'] ?? false,
                'renewal_frequency_months' => $merged['renewal_frequency_months'] ?? null,
                'default_due_days' => $merged['default_due_days'] ?? null,
                'metadata' => $merged['metadata'] ?? null,
                'status' => $merged['status'],
                'updated_by_user_id' => $actor->id,
            ]);
            $item->save();

            $this->auditLogger->record(
                eventType: 'learning.item.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $item->only([
                        'code',
                        'title',
                        'description',
                        'category',
                        'delivery_mode',
                        'duration_minutes',
                        'requires_completion_evidence',
                        'renewal_frequency_months',
                        'default_due_days',
                        'metadata',
                        'status',
                    ]),
                ],
                entityType: 'learning_item',
                entityId: (string) $item->id,
            );

            return $this->accessScopeService->resolveAccessibleItem($actor, $item->id);
        });
    }

    /**
     * @param  LearningAssignmentPayload  $payload
     */
    public function createAssignment(User $actor, array $payload): LearningAssignment
    {
        return DB::transaction(function () use ($actor, $payload): LearningAssignment {
            $item = $this->accessScopeService->resolveAccessibleItem($actor, (int) $payload['learning_item_id']);

            if ($item->status !== 'active') {
                throw ValidationException::withMessages([
                    'learning_item_id' => ['Only active learning items can be assigned.'],
                ]);
            }

            $employees = $this->resolveAudienceEmployees($actor->company_id, $payload);

            if ($employees->isEmpty()) {
                throw ValidationException::withMessages([
                    'audience_rules' => ['The selected audience does not resolve to any active employees.'],
                ]);
            }

            $assignedOn = array_key_exists('assigned_on', $payload)
                ? Carbon::parse((string) $payload['assigned_on'])->startOfDay()
                : now()->startOfDay();

            $completionRules = [
                'requires_completion_evidence' => array_key_exists('requires_completion_evidence', $payload)
                    ? (bool) $payload['requires_completion_evidence']
                    : (bool) $item->requires_completion_evidence,
                'renewal_frequency_months' => array_key_exists('renewal_frequency_months', $payload)
                    ? $payload['renewal_frequency_months']
                    : $item->renewal_frequency_months,
                'default_due_days' => array_key_exists('default_due_days', $payload)
                    ? $payload['default_due_days']
                    : $item->default_due_days,
            ];

            $assignmentDueOn = $this->resolveDueOn($assignedOn, $payload['due_on'] ?? null, $completionRules['default_due_days']);

            if ($assignmentDueOn !== null && $assignmentDueOn->lt($assignedOn)) {
                throw ValidationException::withMessages([
                    'due_on' => ['The due date cannot be earlier than the assignment start date.'],
                ]);
            }

            $assignment = LearningAssignment::query()->create([
                'company_id' => $actor->company_id,
                'learning_item_id' => $item->id,
                'assignment_code' => $this->resolveAssignmentCode($actor->company_id),
                'assigned_by_user_id' => $actor->id,
                'audience_type' => $payload['audience_type'],
                'audience_rules' => $payload['audience_rules'],
                'assigned_on' => $assignedOn->toDateString(),
                'due_on' => $assignmentDueOn?->toDateString(),
                'completion_rules' => $completionRules,
                'notes' => $payload['notes'] ?? null,
                'status' => 'active',
                'target_count' => $employees->count(),
                'completion_count' => 0,
            ]);

            $targetRows = $employees->map(function (Employee $employee) use ($actor, $assignment, $item, $assignedOn, $assignmentDueOn): array {
                return [
                    'company_id' => $actor->company_id,
                    'learning_assignment_id' => $assignment->id,
                    'learning_item_id' => $item->id,
                    'employee_id' => $employee->id,
                    'assigned_by_user_id' => $actor->id,
                    'assigned_on' => $assignedOn->toDateString(),
                    'due_on' => $assignmentDueOn?->toDateString(),
                    'status' => 'assigned',
                    'completion_progress_percent' => 0,
                    'completed_at' => null,
                    'completed_by_user_id' => null,
                    'completion_notes' => null,
                    'completion_evidence' => null,
                    'renewal_due_on' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            LearningAssignmentTarget::query()->insert($targetRows);

            $this->auditLogger->record(
                eventType: 'learning.assignment.created',
                actor: $actor,
                metadata: [
                    'learning_assignment_id' => $assignment->id,
                    'assignment_code' => $assignment->assignment_code,
                    'learning_item_id' => $item->id,
                    'audience_type' => $assignment->audience_type,
                    'target_count' => $assignment->target_count,
                    'due_on' => $assignment->due_on?->toDateString(),
                    'completion_rules' => $assignment->completion_rules,
                ],
                entityType: 'learning_assignment',
                entityId: (string) $assignment->id,
            );

            return $this->accessScopeService->resolveAccessibleAssignment($actor, $assignment->id);
        });
    }

    /**
     * @param  LearningCompleteTargetPayload  $payload
     */
    public function completeTarget(User $actor, int $learningAssignmentTargetId, array $payload): LearningAssignmentTarget
    {
        return DB::transaction(function () use ($actor, $learningAssignmentTargetId, $payload): LearningAssignmentTarget {
            $target = $this->accessScopeService->resolveAccessibleTarget($actor, $learningAssignmentTargetId);

            $this->assertActorCanCompleteTarget($actor, $target);

            $requiresEvidence = $this->trackingStateResolver->requiresCompletionEvidence($target);
            $completionEvidence = $payload['completion_evidence'] ?? null;

            if ($requiresEvidence && empty($completionEvidence)) {
                throw ValidationException::withMessages([
                    'completion_evidence' => ['Completion evidence is required for this learning assignment.'],
                ]);
            }

            if ($target->status === 'completed' && $this->trackingStateResolver->renewalFrequencyMonths($target) === null) {
                throw ValidationException::withMessages([
                    'target' => ['This learning assignment has already been completed.'],
                ]);
            }

            $renewalFrequencyMonths = $this->trackingStateResolver->renewalFrequencyMonths($target);
            $completedAt = now();
            $renewalDueOn = $renewalFrequencyMonths !== null
                ? $completedAt->copy()->addMonthsNoOverflow($renewalFrequencyMonths)->toDateString()
                : null;

            $target->fill([
                'status' => 'completed',
                'completion_progress_percent' => 100,
                'completed_at' => $completedAt,
                'completed_by_user_id' => $actor->id,
                'completion_notes' => $payload['completion_notes'] ?? null,
                'completion_evidence' => $completionEvidence,
                'renewal_due_on' => $renewalDueOn,
            ]);
            $target->save();

            $this->refreshAssignmentSummary($target->learning_assignment_id);

            $this->auditLogger->record(
                eventType: 'learning.target.completed',
                actor: $actor,
                metadata: [
                    'learning_assignment_target_id' => $target->id,
                    'learning_assignment_id' => $target->learning_assignment_id,
                    'learning_item_id' => $target->learning_item_id,
                    'employee_id' => $target->employee_id,
                    'renewal_due_on' => $target->renewal_due_on?->toDateString(),
                    'evidence_present' => ! empty($completionEvidence),
                ],
                entityType: 'learning_assignment_target',
                entityId: (string) $target->id,
            );

            return $this->accessScopeService->resolveAccessibleTarget($actor, $target->id);
        });
    }

    /**
     * @param  Builder<LearningAssignmentTarget>  $query
     * @param  LearningTargetFilters  $filters
     * @return Builder<LearningAssignmentTarget>
     */
    private function buildTargetsQuery(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                array_key_exists('learning_assignment_id', $filters),
                fn (Builder $builder) => $builder->where('learning_assignment_id', $filters['learning_assignment_id']),
            )
            ->when(
                array_key_exists('learning_item_id', $filters),
                fn (Builder $builder) => $builder->where('learning_item_id', $filters['learning_item_id']),
            )
            ->when(
                array_key_exists('employee_id', $filters),
                fn (Builder $builder) => $builder->where('employee_id', $filters['employee_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('q', $filters),
                function (Builder $builder) use ($filters): void {
                    $term = trim((string) $filters['q']);

                    $builder->where(function (Builder $query) use ($term): void {
                        $query->whereHas('item', function (Builder $itemQuery) use ($term): void {
                            $itemQuery->where('code', 'like', '%'.$term.'%')
                                ->orWhere('title', 'like', '%'.$term.'%');
                        })->orWhereHas('employee', function (Builder $employeeQuery) use ($term): void {
                            $employeeQuery->where('employee_code', 'like', '%'.$term.'%')
                                ->orWhere('first_name', 'like', '%'.$term.'%')
                                ->orWhere('last_name', 'like', '%'.$term.'%')
                                ->orWhere('email', 'like', '%'.$term.'%');
                        });
                    });
                },
            )
            ->orderByRaw("case when status = 'assigned' then 0 else 1 end")
            ->orderBy('due_on')
            ->orderByDesc('id');
    }

    private function ensureLearningItemCodeUnique(int $companyId, string $code, ?int $ignoreId = null): void
    {
        $query = LearningItem::query()
            ->where('company_id', $companyId)
            ->where('code', strtoupper(trim($code)));

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'code' => ['This learning item code is already in use for the tenant.'],
            ]);
        }
    }

    /**
     * @param  LearningAssignmentPayload  $payload
     * @return Collection<int, Employee>
     */
    private function resolveAudienceEmployees(int $companyId, array $payload): Collection
    {
        $query = Employee::query()
            ->where('company_id', $companyId)
            ->where('employment_status', 'active');

        $employeeIds = array_map(
            static fn (int|string $employeeId): int => (int) $employeeId,
            $payload['audience_rules']['employee_ids'] ?? [],
        );
        $departmentIds = array_map(
            static fn (int|string $departmentId): int => (int) $departmentId,
            $payload['audience_rules']['department_ids'] ?? [],
        );
        $designationIds = array_map(
            static fn (int|string $designationId): int => (int) $designationId,
            $payload['audience_rules']['designation_ids'] ?? [],
        );

        return match ($payload['audience_type']) {
            'employee' => $this->resolveEmployeesByIds($query, $employeeIds),
            'department' => $this->resolveEmployeesByDepartments($companyId, $query, $departmentIds),
            'designation' => $this->resolveEmployeesByDesignations($companyId, $query, $designationIds),
            'all_active' => $query->get(),
            default => new Collection,
        };
    }

    /**
     * @param  Builder<Employee>  $query
     * @param  array<int, int>  $employeeIds
     * @return Collection<int, Employee>
     */
    private function resolveEmployeesByIds(Builder $query, array $employeeIds): Collection
    {
        $employees = $query->whereIn('id', $employeeIds)->get();

        if ($employees->count() !== count($employeeIds)) {
            throw ValidationException::withMessages([
                'audience_rules.employee_ids' => ['One or more selected employees are not active within the tenant.'],
            ]);
        }

        return $employees;
    }

    /**
     * @param  Builder<Employee>  $query
     * @param  array<int, int>  $departmentIds
     * @return Collection<int, Employee>
     */
    private function resolveEmployeesByDepartments(int $companyId, Builder $query, array $departmentIds): Collection
    {
        $this->assertDepartmentsExist($companyId, $departmentIds);

        return $query->whereIn('department_id', $departmentIds)->get();
    }

    /**
     * @param  Builder<Employee>  $query
     * @param  array<int, int>  $designationIds
     * @return Collection<int, Employee>
     */
    private function resolveEmployeesByDesignations(int $companyId, Builder $query, array $designationIds): Collection
    {
        $this->assertDesignationsExist($companyId, $designationIds);

        return $query->whereIn('designation_id', $designationIds)->get();
    }

    /**
     * @param  list<int>  $departmentIds
     */
    private function assertDepartmentsExist(?int $companyId, array $departmentIds): void
    {
        if ($departmentIds === []) {
            throw ValidationException::withMessages([
                'audience_rules.department_ids' => ['At least one department must be selected.'],
            ]);
        }

        $count = Department::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $departmentIds)
            ->count();

        if ($count !== count($departmentIds)) {
            throw ValidationException::withMessages([
                'audience_rules.department_ids' => ['One or more selected departments are not available within the tenant.'],
            ]);
        }
    }

    /**
     * @param  list<int>  $designationIds
     */
    private function assertDesignationsExist(?int $companyId, array $designationIds): void
    {
        if ($designationIds === []) {
            throw ValidationException::withMessages([
                'audience_rules.designation_ids' => ['At least one designation must be selected.'],
            ]);
        }

        $count = Designation::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $designationIds)
            ->count();

        if ($count !== count($designationIds)) {
            throw ValidationException::withMessages([
                'audience_rules.designation_ids' => ['One or more selected designations are not available within the tenant.'],
            ]);
        }
    }

    private function resolveAssignmentCode(int $companyId): string
    {
        do {
            $code = 'LRN-ASG-'.Str::upper(Str::random(8));
        } while (
            LearningAssignment::query()
                ->where('company_id', $companyId)
                ->where('assignment_code', $code)
                ->exists()
        );

        return $code;
    }

    private function resolveDueOn(Carbon $assignedOn, mixed $dueOn, mixed $defaultDueDays): ?Carbon
    {
        if ($dueOn !== null) {
            return Carbon::parse((string) $dueOn)->startOfDay();
        }

        if ($defaultDueDays !== null) {
            return $assignedOn->copy()->addDays((int) $defaultDueDays);
        }

        return null;
    }

    private function refreshAssignmentSummary(int $learningAssignmentId): void
    {
        $targetCount = LearningAssignmentTarget::query()
            ->where('learning_assignment_id', $learningAssignmentId)
            ->count();

        $completionCount = LearningAssignmentTarget::query()
            ->where('learning_assignment_id', $learningAssignmentId)
            ->where('status', 'completed')
            ->count();

        LearningAssignment::query()
            ->whereKey($learningAssignmentId)
            ->update([
                'target_count' => $targetCount,
                'completion_count' => $completionCount,
                'updated_at' => now(),
            ]);
    }

    private function assertActorCanCompleteTarget(User $actor, LearningAssignmentTarget $target): void
    {
        if ($actor->can('learning.manage')) {
            return;
        }

        if ($target->employee?->user_id !== $actor->id) {
            throw ValidationException::withMessages([
                'target' => ['You can only complete learning assignments linked to your own employee profile.'],
            ]);
        }
    }
}
