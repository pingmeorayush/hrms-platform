<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfferRequest extends FormRequest
{
    use AuthorizesRecruitmentRequests;

    public function authorize(): bool
    {
        return $this->canUpdateOfferAction();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'action' => ['sometimes', Rule::in(['submit', 'approve', 'reject', 'request_changes', 'mark_sent', 'record_acceptance', 'record_decline', 'mark_expired'])],
            'comment' => ['nullable', 'string', 'max:2000'],
            'recruiter_user_id' => ['sometimes', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'employment_type' => ['sometimes', Rule::in(['full_time', 'contract', 'intern', 'consultant', 'temporary'])],
            'currency' => ['sometimes', 'string', 'size:3'],
            'annual_ctc_amount' => ['sometimes', 'numeric', 'min:0'],
            'joining_bonus_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'proposed_start_date' => ['sometimes', 'nullable', 'date'],
            'expires_on' => ['sometimes', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'candidate_message' => ['sometimes', 'nullable', 'string', 'max:3000'],
        ];
    }
}
