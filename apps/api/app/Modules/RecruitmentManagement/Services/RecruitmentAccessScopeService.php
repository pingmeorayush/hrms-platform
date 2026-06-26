<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RecruitmentAccessScopeService
{
    /**
     * @param  array<int, string>  $with
     * @return Builder<JobRequisition>
     */
    public function requisitionsQuery(
        User $actor,
        array $with = [
            'department',
            'designation',
            'location',
            'costCenter',
            'recruiter',
            'hiringManager',
            'requestedBy',
            'workflowInstance.tasks.assignee',
            'workflowInstance.tasks.actor',
        ],
    ): Builder {
        $query = JobRequisition::query()->with($with);

        if ($this->canManageAllTenantRecruitment($actor)) {
            return $query;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        return $query->where(function (Builder $builder) use ($actor, $actorEmployee): void {
            $builder->where('recruiter_user_id', $actor->id)
                ->orWhere('requested_by_user_id', $actor->id)
                ->orWhere('created_by_user_id', $actor->id)
                ->orWhereHas(
                    'workflowInstance.tasks',
                    fn (Builder $taskQuery) => $taskQuery->where('assigned_to_user_id', $actor->id),
                );

            if ($actorEmployee) {
                $builder->orWhere('hiring_manager_employee_id', $actorEmployee->id);
            }
        });
    }

    public function resolveAccessibleRequisition(User $actor, int $jobRequisitionId): JobRequisition
    {
        return $this->requisitionsQuery($actor)->findOrFail($jobRequisitionId);
    }

    public function canManageAllTenantRecruitment(User $actor): bool
    {
        return $actor->can('recruitment.manage');
    }

    public function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
