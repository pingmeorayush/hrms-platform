<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListEmployeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'search' => ['nullable', 'string', 'max:120'],
            'employment_status' => ['nullable', Rule::in(['active', 'inactive', 'probation', 'notice_period', 'terminated'])],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'designation_id' => ['nullable', 'integer', Rule::exists('designations', 'id')->where('company_id', $companyId)],
            'manager_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
