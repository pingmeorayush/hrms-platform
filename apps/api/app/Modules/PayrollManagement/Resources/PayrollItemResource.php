<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $employee = $this->whenLoaded('employee');

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $employee ? [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
            ] : null,
            'employee_compensation_id' => $this->employee_compensation_id,
            'status' => $this->status,
            'employment_days' => $this->employment_days,
            'unpaid_days' => $this->unpaid_days,
            'lop_days' => $this->lop_days,
            'overtime_minutes' => $this->overtime_minutes,
            'overtime_earnings' => $this->overtime_earnings,
            'gross_salary' => $this->gross_salary,
            'total_earnings' => $this->total_earnings,
            'total_deductions' => $this->total_deductions,
            'net_salary' => $this->net_salary,
            'employer_cost' => $this->employer_cost,
            'earnings_breakdown' => $this->earnings_breakdown ?? [],
            'deductions_breakdown' => $this->deductions_breakdown ?? [],
            'employer_contribution_breakdown' => $this->employer_contribution_breakdown ?? [],
            'input_snapshot' => $this->input_snapshot ?? [],
            'validation_errors' => $this->validation_errors ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
