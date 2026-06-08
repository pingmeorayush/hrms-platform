<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollInputResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $employee = $this->whenLoaded('employee');

        return [
            'id' => $this->id,
            'payroll_run_id' => $this->payroll_run_id,
            'employee_id' => $this->employee_id,
            'employee' => $employee ? [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
            ] : null,
            'employee_compensation_id' => $this->employee_compensation_id,
            'payroll_adjustment_id' => $this->payroll_adjustment_id,
            'source_type' => $this->source_type,
            'input_code' => $this->input_code,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'effective_date' => $this->effective_date?->toDateString(),
            'source_record_id' => $this->source_record_id,
            'metadata' => $this->metadata ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
