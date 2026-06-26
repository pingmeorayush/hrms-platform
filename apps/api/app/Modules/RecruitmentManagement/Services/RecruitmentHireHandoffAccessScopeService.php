<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Employee;
use App\Models\RecruitmentHireHandoff;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RecruitmentHireHandoffAccessScopeService
{
    /**
     * @param  array<int, string>  $with
     * @return Builder<RecruitmentHireHandoff>
     */
    public function handoffsQuery(
        User $actor,
        array $with = [
            'offer',
            'candidate',
            'requisition',
            'employee',
            'recruiter',
            'convertedBy',
            'sourceResume',
        ],
    ): Builder {
        $query = RecruitmentHireHandoff::query()->with($with);

        if ($actor->can('recruitment.manage') || $actor->can('employee.manage') || $actor->can('employee.view')) {
            return $query;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        return $query->where(function (Builder $builder) use ($actor, $actorEmployee): void {
            $builder->where('recruiter_user_id', $actor->id)
                ->orWhereHas(
                    'offer',
                    fn (Builder $offerQuery) => $offerQuery->where('recruiter_user_id', $actor->id),
                );

            if ($actorEmployee) {
                $builder->orWhereHas(
                    'requisition',
                    fn (Builder $requisitionQuery) => $requisitionQuery->where('hiring_manager_employee_id', $actorEmployee->id),
                );
            }
        });
    }

    public function resolveAccessibleHandoff(User $actor, int $handoffId): RecruitmentHireHandoff
    {
        return $this->handoffsQuery($actor)->findOrFail($handoffId);
    }

    private function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
