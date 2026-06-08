<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $employee = $this->whenLoaded('employee');
        $payrollRun = $this->whenLoaded('payrollRun');

        return [
            'id' => $this->id,
            'payroll_run_id' => $this->payroll_run_id,
            'payroll_period_id' => $this->payroll_period_id,
            'payroll_item_id' => $this->payroll_item_id,
            'employee_id' => $this->employee_id,
            'employee' => $employee ? [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
            ] : $this->employee_snapshot,
            'slip_number' => $this->slip_number,
            'status' => $this->status,
            'currency' => $this->currency,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'payroll_date' => $this->payroll_date?->toDateString(),
            'file_name' => $this->file_name,
            'gross_salary' => $this->gross_salary,
            'total_earnings' => $this->total_earnings,
            'total_deductions' => $this->total_deductions,
            'net_salary' => $this->net_salary,
            'employer_cost' => $this->employer_cost,
            'earnings_breakdown' => $this->earnings_breakdown ?? [],
            'deductions_breakdown' => $this->deductions_breakdown ?? [],
            'employer_contribution_breakdown' => $this->employer_contribution_breakdown ?? [],
            'company_snapshot' => $this->company_snapshot ?? [],
            'rendered_format' => $this->rendered_format,
            'generated_at' => $this->generated_at?->toIso8601String(),
            'payroll_run_status' => $payrollRun?->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
