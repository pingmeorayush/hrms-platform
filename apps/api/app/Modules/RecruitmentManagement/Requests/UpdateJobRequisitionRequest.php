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

class UpdateJobRequisitionRequest extends FormRequest
{
    use AuthorizesRecruitmentRequests;

    public function authorize(): bool
    {
        return $this->canUpdateRequisitionAction();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;
        $jobRequisitionId = (int) $this->route('jobRequisitionId');

        return [
            'action' => ['sometimes', Rule::in(['submit', 'put_on_hold', 'resume', 'close', 'approve', 'reject', 'request_changes'])],
            'comment' => ['nullable', 'string', 'max:2000'],
            'requisition_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('job_requisitions', 'requisition_code')
                    ->where('company_id', $companyId)
                    ->ignore($jobRequisitionId),
            ],
            'title' => ['sometimes', 'string', 'max:150'],
            'employment_type' => ['sometimes', Rule::in(['full_time', 'contract', 'intern', 'consultant', 'temporary'])],
            'hiring_type' => ['sometimes', Rule::in(['new_position', 'backfill', 'replacement'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'openings_count' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'min_experience_years' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:60'],
            'target_start_date' => ['sometimes', 'nullable', 'date'],
            'headcount_reference' => ['sometimes', 'nullable', 'string', 'max:100'],
            'department_id' => ['sometimes', 'nullable', 'integer', Rule::exists(Department::class, 'id')->where('company_id', $companyId)],
            'designation_id' => ['sometimes', 'nullable', 'integer', Rule::exists(Designation::class, 'id')->where('company_id', $companyId)],
            'location_id' => ['sometimes', 'nullable', 'integer', Rule::exists(Location::class, 'id')->where('company_id', $companyId)],
            'cost_center_id' => ['sometimes', 'nullable', 'integer', Rule::exists(CostCenter::class, 'id')->where('company_id', $companyId)],
            'recruiter_user_id' => ['sometimes', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'hiring_manager_employee_id' => ['sometimes', 'integer', Rule::exists(Employee::class, 'id')->where('company_id', $companyId)],
            'justification' => ['sometimes', 'string', 'max:4000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
