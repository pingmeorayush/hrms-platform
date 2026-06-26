<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCandidateStageTransitionRequest extends FormRequest
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
        return [
            'to_stage' => ['required', Rule::in(config('recruitment.candidate_stages', []))],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $toStage = $this->input('to_stage');

                if (in_array($toStage, ['rejected', 'withdrawn'], true) && ! $this->filled('comment')) {
                    $validator->errors()->add('comment', 'A comment is required when rejecting or withdrawing a candidate.');
                }
            },
        ];
    }
}
