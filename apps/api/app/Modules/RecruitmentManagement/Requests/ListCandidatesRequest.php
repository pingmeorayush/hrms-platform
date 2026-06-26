<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\JobRequisition;
use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListCandidatesRequest extends FormRequest
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
            'job_requisition_id' => ['sometimes', 'integer', Rule::exists(JobRequisition::class, 'id')->where('company_id', $companyId)],
            'recruiter_user_id' => ['sometimes', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'current_stage' => ['sometimes', Rule::in(config('recruitment.candidate_stages', []))],
            'status' => ['sometimes', Rule::in(['active', 'hired', 'rejected', 'withdrawn'])],
            'q' => ['sometimes', 'string', 'max:150'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
