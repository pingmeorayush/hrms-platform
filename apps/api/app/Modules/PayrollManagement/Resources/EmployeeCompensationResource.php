<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeCompensationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $employee = $this->whenLoaded('employee');
        $salaryStructure = $this->whenLoaded('salaryStructure');

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $employee ? [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
            ] : null,
            'salary_structure_id' => $this->salary_structure_id,
            'salary_structure' => [
                'id' => $this->salary_structure_id,
                'code' => $this->salary_structure_code,
                'name' => $salaryStructure?->name,
                'version' => $this->salary_structure_version,
                'currency' => $this->currency,
                'pay_frequency' => $this->pay_frequency,
            ],
            'previous_revision_id' => $this->previous_revision_id,
            'revision_reason' => $this->revision_reason,
            'effective_from' => $this->effective_from?->toDateString(),
            'revision_date' => $this->revision_date?->toDateString(),
            'annual_ctc_amount' => $this->annual_ctc_amount,
            'basic_salary_amount' => $this->basic_salary_amount,
            'gross_salary_amount' => $this->gross_salary_amount,
            'net_salary_amount' => $this->net_salary_amount,
            'notes' => $this->notes,
            'component_snapshot' => $this->component_snapshot ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
