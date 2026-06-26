<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInterviewRequest extends FormRequest
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
            'action' => ['required', Rule::in(['cancel'])],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }
}
