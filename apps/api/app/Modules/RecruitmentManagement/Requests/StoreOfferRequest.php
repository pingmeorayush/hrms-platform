<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\Candidate;
use App\Models\JobRequisition;
use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
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
            'candidate_id' => ['required', 'integer', Rule::exists(Candidate::class, 'id')->where('company_id', $companyId)],
            'recruiter_user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'employment_type' => ['required', Rule::in(['full_time', 'contract', 'intern', 'consultant', 'temporary'])],
            'currency' => ['required', 'string', 'size:3'],
            'annual_ctc_amount' => ['required', 'numeric', 'min:0'],
            'joining_bonus_amount' => ['nullable', 'numeric', 'min:0'],
            'proposed_start_date' => ['nullable', 'date'],
            'expires_on' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'candidate_message' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
