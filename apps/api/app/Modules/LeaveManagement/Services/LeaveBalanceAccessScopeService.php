<?php

namespace App\Modules\LeaveManagement\Services;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LeaveBalanceAccessScopeService
{
    /**
     * @param  list<string>  $with
     * @return Builder<LeaveBalance>
     */
    public function balancesQuery(User $actor, array $with = ['employee', 'leaveType']): Builder
    {
        $query = LeaveBalance::query()->with($with);

        if ($this->canViewAllTenantBalances($actor)) {
            return $query;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        if (! $actorEmployee) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->can('leave.approve')) {
            return $query->where(function (Builder $builder) use ($actorEmployee): void {
                $builder->where('employee_id', $actorEmployee->id)
                    ->orWhereHas(
                        'employee',
                        fn (Builder $employeeQuery) => $employeeQuery->where('manager_id', $actorEmployee->id),
                    );
            });
        }

        return $query->where('employee_id', $actorEmployee->id);
    }

    public function resolveAccessibleEmployee(User $actor, int $employeeId): Employee
    {
        $employee = Employee::query()->findOrFail($employeeId);

        if ($this->canViewAllTenantBalances($actor)) {
            return $employee;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        if (! $actorEmployee) {
            throw new NotFoundHttpException;
        }

        if ($employee->id === $actorEmployee->id) {
            return $employee;
        }

        if ($actor->can('leave.approve') && $employee->manager_id === $actorEmployee->id) {
            return $employee;
        }

        throw new NotFoundHttpException;
    }

    public function canViewAllTenantBalances(User $actor): bool
    {
        return $actor->can('leave.manage_balance') || $actor->can('employee.manage');
    }

    public function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
