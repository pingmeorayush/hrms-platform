<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\Employee;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayrollAdjustmentRequest extends FormRequest
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
            'adjustment_code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(['earning', 'deduction', 'reimbursement', 'bonus', 'custom'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'effective_date' => ['required', 'date'],
            'status' => ['sometimes', Rule::in(['active', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
