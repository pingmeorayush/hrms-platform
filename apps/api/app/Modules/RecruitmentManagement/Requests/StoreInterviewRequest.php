<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Models\Candidate;
use App\Models\JobRequisition;
use App\Models\User;
use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInterviewRequest extends FormRequest
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
            'interviewer_user_id' => ['required', 'integer', Rule::exists(User::class, 'id')->where('company_id', $companyId)],
            'round_number' => ['required', 'integer', 'min:1', 'max:20'],
            'interview_type' => ['required', Rule::in(['screening', 'technical', 'managerial', 'hr', 'culture'])],
            'timezone' => ['required', 'string', 'max:100'],
            'scheduled_start_at' => ['required', 'date'],
            'scheduled_end_at' => ['required', 'date', 'after:scheduled_start_at'],
            'meeting_mode' => ['required', Rule::in(['virtual', 'onsite', 'phone'])],
            'meeting_location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'agenda' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
