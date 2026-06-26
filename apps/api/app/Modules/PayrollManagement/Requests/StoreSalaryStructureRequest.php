<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\SalaryComponent;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalaryStructureRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'currency' => ['required', 'string', 'max:10'],
            'country_code' => ['required', 'string', 'size:2'],
            'pay_frequency' => ['required', Rule::in(['monthly', 'weekly', 'biweekly', 'semi_monthly', 'custom'])],
            'grade' => ['nullable', 'string', 'max:30'],
            'band' => ['nullable', 'string', 'max:30'],
            'level' => ['nullable', 'string', 'max:30'],
            'annual_ctc_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'basic_salary_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'gross_salary_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'net_salary_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'effective_from' => ['required', 'date'],
            'revision_date' => ['required', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.salary_component_id' => [
                'required',
                'integer',
                Rule::exists(SalaryComponent::class, 'id')->where('company_id', $companyId),
            ],
            'components.*.display_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'components.*.configured_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'components.*.configured_percentage' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'components.*.configured_basis_component_codes' => ['nullable', 'array'],
            'components.*.configured_basis_component_codes.*' => ['string', 'max:30', 'distinct'],
            'components.*.configured_expression_formula' => ['nullable', 'string', 'max:500'],
        ];
    }
}
