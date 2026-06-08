<?php

namespace App\Modules\LeaveManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveAccrualResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => new EmployeeReferenceResource($this->whenLoaded('employee')),
            'leave_policy_id' => $this->leave_policy_id,
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'policy_version' => $this->policy_version,
            'accrual_frequency' => $this->accrual_frequency,
            'period' => [
                'start' => $this->period_start?->toDateString(),
                'end' => $this->period_end?->toDateString(),
            ],
            'calculation_hash' => $this->calculation_hash,
            'status' => $this->status,
            'is_eligible' => $this->is_eligible,
            'balances' => [
                'opening_balance_days' => $this->opening_balance_days,
                'accrued_days' => $this->accrued_days,
                'carry_forward_days' => $this->carry_forward_days,
                'encashable_days' => $this->encashable_days,
                'used_days_in_period' => $this->used_days_in_period,
                'projected_closing_balance_days' => $this->projected_closing_balance_days,
            ],
            'eligibility_snapshot' => (object) ($this->eligibility_snapshot ?? []),
            'projected_encashment' => $this->whenLoaded('projectedEncashment', function (): ?array {
                if ($this->projectedEncashment === null) {
                    return null;
                }

                return [
                    'id' => $this->projectedEncashment->id,
                    'projected_days' => $this->projectedEncashment->projected_days,
                    'status' => $this->projectedEncashment->status,
                    'cycle_start' => $this->projectedEncashment->cycle_start?->toDateString(),
                    'cycle_end' => $this->projectedEncashment->cycle_end?->toDateString(),
                    'metadata' => (object) ($this->projectedEncashment->metadata ?? []),
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
