<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\Offer;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListRecruitmentHireHandoffsRequest extends FormRequest
{
    use AuthorizesRecruitmentRequests;

    public function authorize(): bool
    {
        return $this->canAccessRecruitmentWorkspace() || $this->canAccessEmployeeWorkspace();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'job_requisition_id' => ['sometimes', 'integer', Rule::exists(JobRequisition::class, 'id')->where('company_id', $companyId)],
            'candidate_id' => ['sometimes', 'integer', Rule::exists(Candidate::class, 'id')->where('company_id', $companyId)],
            'offer_id' => ['sometimes', 'integer', Rule::exists(Offer::class, 'id')->where('company_id', $companyId)],
            'employee_id' => ['sometimes', 'integer', Rule::exists(Employee::class, 'id')->where('company_id', $companyId)],
            'status' => ['sometimes', Rule::in(['employee_created', 'onboarding_queued', 'onboarding_skipped'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
