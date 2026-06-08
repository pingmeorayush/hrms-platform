<?php

namespace App\Modules\AttendanceManagement\Services;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AttendanceAccessScopeService
{
    public function attendanceRecordsQuery(
        User $actor,
        array $with = ['employee', 'shift'],
        bool $includeSelfForApprovers = true,
    ): Builder {
        $query = AttendanceRecord::query()->with($with);

        if ($this->canViewAllTenantAttendance($actor)) {
            return $query;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        if (! $actorEmployee) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->can('attendance.approve')) {
            return $query->where(function (Builder $builder) use ($actorEmployee, $includeSelfForApprovers): void {
                if ($includeSelfForApprovers) {
                    $builder->where('employee_id', $actorEmployee->id)
                        ->orWhereHas(
                            'employee',
                            fn (Builder $employeeQuery) => $employeeQuery->where('manager_id', $actorEmployee->id),
                        );

                    return;
                }

                $builder->whereHas(
                    'employee',
                    fn (Builder $employeeQuery) => $employeeQuery->where('manager_id', $actorEmployee->id),
                );
            });
        }

        return $query->where('employee_id', $actorEmployee->id);
    }

    public function attendanceCorrectionsQuery(
        User $actor,
        array $with = [
            'employee',
            'requester',
            'latestActor',
            'workflowInstance.tasks.assignee',
            'workflowInstance.tasks.actor',
        ],
        bool $includeSelfForApprovers = true,
    ): Builder {
        $query = AttendanceCorrection::query()->with($with);

        if ($this->canViewAllTenantAttendance($actor)) {
            return $query;
        }

        $actorEmployee = $this->findLinkedEmployee($actor);

        if (! $actorEmployee) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->can('attendance.approve')) {
            return $query->where(function (Builder $builder) use ($actorEmployee, $includeSelfForApprovers): void {
                if ($includeSelfForApprovers) {
                    $builder->where('employee_id', $actorEmployee->id)
                        ->orWhereHas(
                            'employee',
                            fn (Builder $employeeQuery) => $employeeQuery->where('manager_id', $actorEmployee->id),
                        );

                    return;
                }

                $builder->whereHas(
                    'employee',
                    fn (Builder $employeeQuery) => $employeeQuery->where('manager_id', $actorEmployee->id),
                );
            });
        }

        return $query->where('employee_id', $actorEmployee->id);
    }

    public function canViewAllTenantAttendance(User $actor): bool
    {
        return $actor->can('attendance.edit')
            || $actor->can('attendance.manage_shift')
            || $actor->can('attendance.manage_roster');
    }

    public function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
