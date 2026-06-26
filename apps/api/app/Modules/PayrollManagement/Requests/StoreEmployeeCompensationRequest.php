<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeCompensationRequest extends FormRequest
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
            'employee_id' => [
                'required',
                'integer',
                Rule::exists(Employee::class, 'id')->where('company_id', $companyId),
            ],
            'salary_structure_id' => [
                'required',
                'integer',
                Rule::exists(SalaryStructure::class, 'id')->where('company_id', $companyId),
            ],
            'revision_reason' => [
                'required',
                Rule::in([
                    'initial_assignment',
                    'annual_revision',
                    'promotion',
                    'transfer',
                    'market_adjustment',
                    'correction',
                    'manual_change',
                ]),
            ],
            'effective_from' => ['required', 'date'],
            'revision_date' => ['required', 'date', 'after_or_equal:effective_from'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
