<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobRequisitionRequest extends FormRequest
{
    use AuthorizesRecruitmentRequests;

    public function authorize(): bool
    {
        return $this->canManageRecruitment();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'requisition_code' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('job_requisitions', 'requisition_code')->where('company_id', $companyId),
            ],
            'title' => ['required', 'string', 'max:150'],
            'employment_type' => ['required', Rule::in(['full_time', 'contract', 'intern', 'consultant', 'temporary'])],
            'hiring_type' => ['required', Rule::in(['new_position', 'backfill', 'replacement'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'openings_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'min_experience_years' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'target_start_date' => ['nullable', 'date'],
            'headcount_reference' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'integer', Rule::exists(Department::class, 'id')->where('company_id', $companyId)],
            'designation_id' => ['nullable', 'integer', Rule::exists(Designation::class, 'id')->where('company_id', $companyId)],
            'location_id' => ['nullable', 'integer', Rule::exists(Location::class, 'id')->where('company_id', $companyId)],
            'cost_center_id' => ['nullable', 'integer', Rule::exists(CostCenter::class, 'id')->where('company_id', $companyId)],
            'recruiter_user_id' => ['required', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'hiring_manager_employee_id' => ['required', 'integer', Rule::exists(Employee::class, 'id')->where('company_id', $companyId)],
            'justification' => ['required', 'string', 'max:4000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
