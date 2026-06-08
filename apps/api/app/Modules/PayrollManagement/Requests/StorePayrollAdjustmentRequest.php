<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayrollAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
