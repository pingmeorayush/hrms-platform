<?php

namespace App\Modules\LearningManagement\Services;

use App\Models\Employee;
use App\Models\LearningAssignment;
use App\Models\LearningAssignmentTarget;
use App\Models\LearningItem;
use App\Models\User;
use App\Modules\EmployeeManagement\Services\EmployeeSelfServiceAccessScopeService;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LearningAccessScopeService
{
    public function __construct(
        private readonly EmployeeSelfServiceAccessScopeService $selfServiceAccessScopeService,
    ) {}

    /**
     * @return Builder<LearningItem>
     */
    public function itemsQuery(User $actor): Builder
    {
        return LearningItem::query()
            ->with(['createdBy', 'updatedBy'])
            ->where('company_id', $actor->company_id);
    }

    /**
     * @return Builder<LearningAssignment>
     */
    public function assignmentsQuery(User $actor): Builder
    {
        $accessibleEmployeeIds = $this->accessibleEmployeeIds($actor);

        $query = LearningAssignment::query()
            ->with(['item', 'assignedBy'])
            ->where('company_id', $actor->company_id);

        if ($accessibleEmployeeIds === null) {
            return $query;
        }

        if ($accessibleEmployeeIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->with([
                'targets' => function ($builder) use ($accessibleEmployeeIds): void {
                    $builder->whereIn('employee_id', $accessibleEmployeeIds);
                },
            ])
            ->whereHas('targets', fn (Builder $builder) => $builder->whereIn('employee_id', $accessibleEmployeeIds));
    }

    /**
     * @return Builder<LearningAssignmentTarget>
     */
    public function targetsQuery(User $actor): Builder
    {
        $query = LearningAssignmentTarget::query()
            ->with([
                'assignment.item',
                'assignment.assignedBy',
                'item',
                'employee.department',
                'employee.designation',
                'completedBy',
            ])
            ->where('company_id', $actor->company_id);

        $accessibleEmployeeIds = $this->accessibleEmployeeIds($actor);

        if ($accessibleEmployeeIds === null) {
            return $query;
        }

        if ($accessibleEmployeeIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('employee_id', $accessibleEmployeeIds);
    }

    /**
     * @return Builder<LearningAssignmentTarget>
     */
    public function myTargetsQuery(User $actor): Builder
    {
        $linkedEmployee = $this->selfServiceAccessScopeService->findLinkedEmployee($actor);

        $query = LearningAssignmentTarget::query()
            ->with([
                'assignment.item',
                'assignment.assignedBy',
                'item',
                'employee.department',
                'employee.designation',
                'completedBy',
            ])
            ->where('company_id', $actor->company_id);

        if (! $linkedEmployee) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('employee_id', $linkedEmployee->id);
    }

    public function resolveAccessibleItem(User $actor, int $learningItemId): LearningItem
    {
        $item = $this->itemsQuery($actor)->find($learningItemId);

        if (! $item) {
            throw new NotFoundHttpException;
        }

        return $item;
    }

    public function resolveAccessibleAssignment(User $actor, int $learningAssignmentId): LearningAssignment
    {
        $accessibleEmployeeIds = $this->accessibleEmployeeIds($actor);

        $assignment = $this->assignmentsQuery($actor)
            ->with([
                'targets' => function ($builder) use ($accessibleEmployeeIds): void {
                    if (is_array($accessibleEmployeeIds)) {
                        if ($accessibleEmployeeIds === []) {
                            $builder->whereRaw('1 = 0');

                            return;
                        }

                        $builder->whereIn('employee_id', $accessibleEmployeeIds);
                    }

                    $builder->with(['employee.department', 'employee.designation', 'completedBy']);
                },
            ])
            ->find($learningAssignmentId);

        if (! $assignment) {
            throw new NotFoundHttpException;
        }

        return $assignment;
    }

    public function resolveAccessibleTarget(User $actor, int $learningAssignmentTargetId): LearningAssignmentTarget
    {
        $target = $this->targetsQuery($actor)->find($learningAssignmentTargetId);

        if (! $target) {
            throw new NotFoundHttpException;
        }

        return $target;
    }

    /**
     * @return list<int>|null
     */
    private function accessibleEmployeeIds(User $actor): ?array
    {
        if ($actor->can('learning.manage') || $actor->can('learning.assign')) {
            return null;
        }

        $linkedEmployee = $this->selfServiceAccessScopeService->findLinkedEmployee($actor);

        if (! $linkedEmployee) {
            return [];
        }

        if ($actor->hasRole('manager')) {
            return collect([$linkedEmployee->id])
                ->merge(Employee::query()
                    ->where('company_id', $actor->company_id)
                    ->where('manager_id', $linkedEmployee->id)
                    ->pluck('id'))
                ->unique()
                ->values()
                ->all();
        }

        return [$linkedEmployee->id];
    }
}
