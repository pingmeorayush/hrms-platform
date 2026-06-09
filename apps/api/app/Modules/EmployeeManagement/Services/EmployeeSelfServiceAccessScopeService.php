<?php

namespace App\Modules\EmployeeManagement\Services;

use App\Models\Employee;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EmployeeSelfServiceAccessScopeService
{
    public function resolveLinkedEmployee(User $actor): Employee
    {
        $employee = $this->findLinkedEmployee($actor);

        if (! $employee) {
            throw new NotFoundHttpException;
        }

        return $employee;
    }

    public function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()
            ->where('user_id', $actor->id)
            ->first();
    }
}
