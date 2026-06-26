<?php

namespace App\Modules\RecruitmentManagement\Requests;

use App\Modules\RecruitmentManagement\Requests\Concerns\AuthorizesRecruitmentRequests;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateResumeRequest extends FormRequest
{
    use AuthorizesRecruitmentRequests;

    public function authorize(): bool
    {
        return $this->canManageRecruitment();
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $allowedExtensions = implode(',', config('recruitment.resume_allowed_extensions', []));
        $maxFileSizeKb = (int) config('recruitment.resume_max_file_size_kb', 10240);

        return [
            'notes' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimes:'.$allowedExtensions, 'max:'.$maxFileSizeKb],
        ];
    }
}
