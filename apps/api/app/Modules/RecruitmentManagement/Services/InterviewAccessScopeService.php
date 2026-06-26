<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Employee;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class InterviewAccessScopeService
{
    /**
     * @param  array<int, string>  $with
     * @return Builder<Interview>
     */
    public function interviewsQuery(
        User $actor,
        array $with = [
            'candidate.recruiter',
            'requisition',
            'interviewer',
            'feedback.interviewer',
        ],
    ): Builder {
        $query = Interview::query()->with($with);

        if ($actor->can('recruitment.manage')) {
            return $query;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        return $query->where(function (Builder $builder) use ($actor, $actorEmployee): void {
            $builder->where('interviewer_user_id', $actor->id)
                ->orWhereHas(
                    'candidate',
                    fn (Builder $candidateQuery) => $candidateQuery->where('recruiter_user_id', $actor->id),
                );

            if ($actorEmployee) {
                $builder->orWhereHas(
                    'requisition',
                    fn (Builder $requisitionQuery) => $requisitionQuery->where('hiring_manager_employee_id', $actorEmployee->id),
                );
            }
        });
    }

    public function resolveAccessibleInterview(User $actor, int $interviewId): Interview
    {
        return $this->interviewsQuery($actor)->findOrFail($interviewId);
    }

    private function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
