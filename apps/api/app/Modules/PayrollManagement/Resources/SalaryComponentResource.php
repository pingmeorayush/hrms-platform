<?php

namespace App\Modules\PayrollManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category,
            'calculation_type' => $this->calculation_type,
            'default_formula_inputs' => [
                'flat_amount' => $this->flat_amount,
                'percentage_value' => $this->percentage_value,
                'percentage_basis_component_codes' => $this->percentage_basis_component_codes ?? [],
                'expression_formula' => $this->expression_formula,
            ],
            'is_taxable' => (bool) $this->is_taxable,
            'is_proratable' => (bool) $this->is_proratable,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
