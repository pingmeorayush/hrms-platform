<?php

namespace App\Modules\LeaveManagement\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewLeaveAccrualRequest extends FormRequest
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
            'period_start' => ['required', 'date'],
            'unused_balance_days' => ['nullable', 'numeric', 'min:0', 'max:3650'],
            'used_days_in_period' => ['nullable', 'numeric', 'min:0', 'max:3650'],
        ];
    }
}
