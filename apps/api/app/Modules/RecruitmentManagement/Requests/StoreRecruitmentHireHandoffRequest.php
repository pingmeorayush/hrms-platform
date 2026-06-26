<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeLifecycleTaskTemplate;
use App\Models\Location;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecruitmentHireHandoffRequest extends FormRequest
{
    use AuthorizesRecruitmentRequests;

    public function authorize(): bool
    {
        return $this->canManageEmployees();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'employee_code' => ['nullable', 'string', 'max:50'],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female', 'non_binary', 'prefer_not_to_say'])],
            'marital_status' => ['sometimes', 'nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'date_of_joining' => ['sometimes', 'date'],
            'employment_status' => ['sometimes', Rule::in(['active', 'inactive', 'probation', 'notice_period'])],
            'department_id' => ['sometimes', 'integer', Rule::exists(Department::class, 'id')->where('company_id', $companyId)],
            'designation_id' => ['sometimes', 'integer', Rule::exists(Designation::class, 'id')->where('company_id', $companyId)],
            'manager_id' => ['sometimes', 'nullable', Rule::exists(Employee::class, 'id')->where('company_id', $companyId)],
            'location_id' => ['sometimes', 'nullable', 'integer', Rule::exists(Location::class, 'id')->where('company_id', $companyId)],
            'cost_center_id' => ['sometimes', 'nullable', 'integer', Rule::exists(CostCenter::class, 'id')->where('company_id', $companyId)],
            'trigger_onboarding' => ['sometimes', 'boolean'],
            'template_ids' => ['sometimes', 'array'],
            'template_ids.*' => [
                'integer',
                Rule::exists(EmployeeLifecycleTaskTemplate::class, 'id')
                    ->where('company_id', $companyId)
                    ->where('lifecycle_type', 'onboarding')
                    ->where('is_active', true),
            ],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
