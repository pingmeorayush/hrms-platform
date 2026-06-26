<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidateRequest extends FormRequest
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
            'recruiter_user_id' => ['sometimes', 'nullable', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'email' => ['sometimes', 'email:rfc', 'max:150'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'source' => ['sometimes', Rule::in(['manual', 'career_portal', 'referral', 'agency', 'campus', 'social'])],
            'total_experience_years' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:60'],
            'notice_period_days' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:365'],
            'current_company' => ['sometimes', 'nullable', 'string', 'max:150'],
            'current_title' => ['sometimes', 'nullable', 'string', 'max:150'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
