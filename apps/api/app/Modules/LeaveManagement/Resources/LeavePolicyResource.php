<?php

namespace App\Modules\LeaveManagement\Resources;

use App\Modules\OrganizationManagement\Resources\DepartmentResource;
use App\Modules\OrganizationManagement\Resources\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeavePolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'leave_type_id' => $this->leave_type_id,
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'version' => $this->version,
            'scope_key' => $this->scope_key,
            'annual_allowance_days' => $this->annual_allowance_days,
            'opening_balance_days' => $this->opening_balance_days,
            'accrual_frequency' => $this->accrual_frequency,
            'carry_forward_limit_days' => $this->carry_forward_limit_days,
            'encashment_limit_days' => $this->encashment_limit_days,
            'max_consecutive_days' => $this->max_consecutive_days,
            'min_notice_days' => $this->min_notice_days,
            'requires_documentation_after_days' => $this->requires_documentation_after_days,
            'applicable_department' => new DepartmentResource($this->whenLoaded('applicableDepartment')),
            'applicable_location' => new LocationResource($this->whenLoaded('applicableLocation')),
            'eligibility_rule' => $this->eligibility_rule ?? [
                'employment_types' => [],
                'employment_statuses' => [],
                'genders' => [],
                'marital_statuses' => [],
                'minimum_tenure_days' => null,
            ],
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
