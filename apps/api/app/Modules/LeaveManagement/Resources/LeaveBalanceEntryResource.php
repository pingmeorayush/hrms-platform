<?php

namespace App\Modules\LeaveManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'leave_balance_id' => $this->leave_balance_id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'entry_type' => $this->entry_type,
            'quantity_days' => $this->quantity_days,
            'balance_before_days' => $this->balance_before_days,
            'balance_after_days' => $this->balance_after_days,
            'effective_on' => $this->effective_on?->toDateString(),
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'metadata' => (object) ($this->metadata ?? []),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
