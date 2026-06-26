<?php

namespace App\Modules\EmployeeManagement\Requests\Concerns;

use App\Models\User;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;

trait AuthorizesEmployeeRequests
{
    use AuthorizesRoutePermissions;

    protected function employeeRequestUser(): ?User
    {
        $user = $this->user();

        return $user instanceof User ? $user : null;
    }
}
