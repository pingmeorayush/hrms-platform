<?php

namespace App\Modules\Platform\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'initials' => $this->initials,
            'email' => $this->email,
            'is_active' => (bool) $this->is_active,
            'requires_mfa' => (bool) $this->requires_mfa,
            'mfa_method' => $this->mfa_method,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'locked_until' => $this->locked_until?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'roles' => $this->getRoleNames()->sort()->values()->all(),
            'employee' => $this->employee
                ? [
                    'id' => $this->employee->id,
                    'employee_code' => $this->employee->employee_code,
                    'full_name' => $this->employee->full_name,
                    'email' => $this->employee->email,
                ]
                : null,
        ];
    }
}
