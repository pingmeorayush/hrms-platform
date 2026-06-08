<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryStructureComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $component = $this->whenLoaded('salaryComponent');

        return [
            'id' => $this->id,
            'salary_component_id' => $this->salary_component_id,
            'salary_component' => new SalaryComponentResource($component),
            'display_order' => $this->display_order,
            'resolved_formula_inputs' => [
                'calculation_type' => $component?->calculation_type,
                'flat_amount' => $this->configured_amount ?? $component?->flat_amount,
                'percentage_value' => $this->configured_percentage ?? $component?->percentage_value,
                'percentage_basis_component_codes' => $this->configured_basis_component_codes
                    ?? $component?->percentage_basis_component_codes
                    ?? [],
                'expression_formula' => $this->configured_expression_formula ?? $component?->expression_formula,
            ],
        ];
    }
}
