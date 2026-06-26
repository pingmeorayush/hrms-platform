<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Employee;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OfferAccessScopeService
{
    /**
     * @param  array<int, string>  $with
     * @return Builder<Offer>
     */
    public function offersQuery(
        User $actor,
        array $with = [
            'candidate.recruiter',
            'requisition',
            'recruiter',
            'requestedBy',
            'handoff.employee',
            'workflowInstance.tasks.assignee',
            'workflowInstance.tasks.actor',
            'decisions.actor',
        ],
    ): Builder {
        $query = Offer::query()->with($with);

        if ($actor->can('recruitment.manage')) {
            return $query;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        return $query->where(function (Builder $builder) use ($actor, $actorEmployee): void {
            $builder->where('recruiter_user_id', $actor->id)
                ->orWhere('requested_by_user_id', $actor->id)
                ->orWhereHas(
                    'workflowInstance.tasks',
                    fn (Builder $taskQuery) => $taskQuery->where('assigned_to_user_id', $actor->id),
                );

            if ($actorEmployee) {
                $builder->orWhereHas(
                    'requisition',
                    fn (Builder $requisitionQuery) => $requisitionQuery->where('hiring_manager_employee_id', $actorEmployee->id),
                );
            }
        });
    }

    public function resolveAccessibleOffer(User $actor, int $offerId): Offer
    {
        return $this->offersQuery($actor)->findOrFail($offerId);
    }

    private function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
