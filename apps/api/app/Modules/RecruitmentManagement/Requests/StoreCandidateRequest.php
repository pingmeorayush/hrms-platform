<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\JobRequisition;
use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCandidateRequest extends FormRequest
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
            'job_requisition_id' => ['required', 'integer', Rule::exists(JobRequisition::class, 'id')->where('company_id', $companyId)],
            'recruiter_user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['required', Rule::in(['manual', 'career_portal', 'referral', 'agency', 'campus', 'social'])],
            'current_stage' => ['sometimes', Rule::in(config('recruitment.candidate_stages', []))],
            'total_experience_years' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'notice_period_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'current_company' => ['nullable', 'string', 'max:150'],
            'current_title' => ['nullable', 'string', 'max:150'],
            'summary' => ['nullable', 'string', 'max:4000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
