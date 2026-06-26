<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListJobRequisitionsRequest extends FormRequest
{
    use AuthorizesRecruitmentRequests;

    public function authorize(): bool
    {
        return $this->canAccessRecruitmentWorkspace();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'status' => ['sometimes', Rule::in(['draft', 'submitted', 'approved', 'on_hold', 'closed', 'rejected', 'changes_requested'])],
            'department_id' => ['sometimes', 'integer', Rule::exists(Department::class, 'id')->where('company_id', $companyId)],
            'location_id' => ['sometimes', 'integer', Rule::exists(Location::class, 'id')->where('company_id', $companyId)],
            'recruiter_user_id' => ['sometimes', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'hiring_manager_employee_id' => ['sometimes', 'integer', Rule::exists(Employee::class, 'id')->where('company_id', $companyId)],
            'employment_type' => ['sometimes', Rule::in(['full_time', 'contract', 'intern', 'consultant', 'temporary'])],
            'hiring_type' => ['sometimes', Rule::in(['new_position', 'backfill', 'replacement'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
