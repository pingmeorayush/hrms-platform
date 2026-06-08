<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPayslipsRequest extends FormRequest
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
                'sometimes',
                'integer',
                Rule::exists(Employee::class, 'id')->where('company_id', $companyId),
            ],
            'payroll_run_id' => [
                'sometimes',
                'integer',
                Rule::exists(PayrollRun::class, 'id')->where('company_id', $companyId),
            ],
            'payroll_period_id' => [
                'sometimes',
                'integer',
                Rule::exists(PayrollPeriod::class, 'id')->where('company_id', $companyId),
            ],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
