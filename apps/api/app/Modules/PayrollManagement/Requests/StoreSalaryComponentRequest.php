<?php

namespace App\Modules\PayrollManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', Rule::in(['earning', 'deduction', 'employer_contribution'])],
            'calculation_type' => ['required', Rule::in(['fixed', 'percentage', 'expression'])],
            'flat_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'percentage_value' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'percentage_basis_component_codes' => ['nullable', 'array'],
            'percentage_basis_component_codes.*' => ['string', 'max:30', 'distinct'],
            'expression_formula' => ['nullable', 'string', 'max:500'],
            'is_taxable' => ['sometimes', 'boolean'],
            'is_proratable' => ['sometimes', 'boolean'],
            'display_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $calculationType = (string) $this->input('calculation_type');
                $flatAmount = $this->input('flat_amount');
                $percentageValue = $this->input('percentage_value');
                $percentageBasis = $this->input('percentage_basis_component_codes', []);
                $expressionFormula = $this->input('expression_formula');

                if ($calculationType === 'fixed' && $flatAmount === null) {
                    $validator->errors()->add('flat_amount', 'A fixed salary component requires a flat amount.');
                }

                if ($calculationType === 'percentage') {
                    if ($percentageValue === null) {
                        $validator->errors()->add('percentage_value', 'A percentage salary component requires a percentage value.');
                    }

                    if (! is_array($percentageBasis) || count($percentageBasis) === 0) {
                        $validator->errors()->add('percentage_basis_component_codes', 'A percentage salary component requires at least one basis component code.');
                    }
                }

                if ($calculationType === 'expression' && blank($expressionFormula)) {
                    $validator->errors()->add('expression_formula', 'An expression-based salary component requires an expression formula.');
                }
            },
        ];
    }
}
