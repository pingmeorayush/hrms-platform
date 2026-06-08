<?php

namespace App\Modules\LeaveManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeReferenceResource($this->whenLoaded('employee')),
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'leave_policy_id' => $this->leave_policy_id,
            'policy_version' => $this->policy_version,
            'available_days' => $this->available_days,
            'booked_days' => $this->booked_days,
            'used_days' => $this->used_days,
            'accrued_days' => $this->accrued_days,
            'carry_forward_days' => $this->carry_forward_days,
            'projected_encashable_days' => $this->projected_encashable_days,
            'current_period_start' => $this->current_period_start?->toDateString(),
            'current_period_end' => $this->current_period_end?->toDateString(),
            'last_calculation_hash' => $this->last_calculation_hash,
            'status' => $this->status,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
