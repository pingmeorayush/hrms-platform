<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoteEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'effective_date' => ['required', 'date'],
            'designation_id' => ['required', Rule::exists('designations', 'id')->where('company_id', $companyId)],
            'department_id' => ['sometimes', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'manager_id' => ['sometimes', 'nullable', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'location_id' => ['sometimes', 'nullable', Rule::exists('locations', 'id')->where('company_id', $companyId)],
            'cost_center_id' => ['sometimes', 'nullable', Rule::exists('cost_centers', 'id')->where('company_id', $companyId)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'termination_date' => ['prohibited'],
            'reason' => ['prohibited'],
        ];
    }
}
