<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\Candidate;
use App\Models\JobRequisition;
use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOffersRequest extends FormRequest
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
            'candidate_id' => ['sometimes', 'integer', Rule::exists(Candidate::class, 'id')->where('company_id', $companyId)],
            'recruiter_user_id' => ['sometimes', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'status' => ['sometimes', Rule::in(['draft', 'submitted', 'approved', 'rejected', 'changes_requested', 'sent', 'accepted', 'declined', 'expired'])],
            'expires_on_from' => ['sometimes', 'date'],
            'expires_on_to' => ['sometimes', 'date', 'after_or_equal:expires_on_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
