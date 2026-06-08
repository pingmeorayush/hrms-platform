<?php

namespace App\Modules\LeaveManagement\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LeaveRequestAccessScopeService
{
    public function requestsQuery(
        User $actor,
        array $with = [
            'employee',
            'department',
            'location',
            'leaveType',
            'workflowInstance.definition',
            'workflowInstance.tasks.assignee',
            'workflowInstance.tasks.actor',
        ],
    ): Builder {
        $query = LeaveRequest::query()->with($with);

        if ($this->canViewAllTenantRequests($actor)) {
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

    public function resolveAccessibleRequest(User $actor, int $leaveRequestId): LeaveRequest
    {
        return $this->requestsQuery($actor)->findOrFail($leaveRequestId);
    }

    public function resolveLinkedEmployee(User $actor): Employee
    {
        $employee = $this->findLinkedEmployee($actor);

        if (! $employee) {
            throw new NotFoundHttpException;
        }

        return $employee;
    }

    public function canViewAllTenantRequests(User $actor): bool
    {
        return $actor->can('employee.manage')
            || $actor->can('leave.manage_balance')
            || $actor->can('leave.manage_policy');
    }

    public function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
