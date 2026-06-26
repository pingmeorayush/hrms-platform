<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CandidateAccessScopeService
{
    /**
     * @param  array<int, string>  $with
     * @return Builder<Candidate>
     */
    public function candidatesQuery(
        User $actor,
        array $with = [
            'requisition',
            'recruiter',
            'resumes.uploader',
            'stageTransitions.actor',
        ],
    ): Builder {
        $query = Candidate::query()->with($with)->withCount('resumes');

        if ($this->canManageAllTenantCandidates($actor)) {
            return $query;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        return $query->where(function (Builder $builder) use ($actor, $actorEmployee): void {
            $builder->where('recruiter_user_id', $actor->id)
                ->orWhereHas(
                    'requisition',
                    fn (Builder $requisitionQuery) => $requisitionQuery->where('recruiter_user_id', $actor->id),
                );

            if ($actorEmployee) {
                $builder->orWhereHas(
                    'requisition',
                    fn (Builder $requisitionQuery) => $requisitionQuery->where('hiring_manager_employee_id', $actorEmployee->id),
                );
            }
        });
    }

    public function resolveAccessibleCandidate(User $actor, int $candidateId): Candidate
    {
        return $this->candidatesQuery($actor)->findOrFail($candidateId);
    }

    public function canManageAllTenantCandidates(User $actor): bool
    {
        return $actor->can('recruitment.manage');
    }

    private function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
