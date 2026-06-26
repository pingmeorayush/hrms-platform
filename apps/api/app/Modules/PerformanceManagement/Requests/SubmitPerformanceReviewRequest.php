<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitPerformanceReviewRequest extends FormRequest
{
    use AuthorizesPerformanceRequests;

    public function authorize(): bool
    {
        return $this->canSubmitPerformanceReview();
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.key' => ['required', 'string', 'max:64'],
            'sections.*.rating' => ['required', 'numeric', 'gt:0'],
            'sections.*.comment' => ['nullable', 'string'],
            'competencies' => ['sometimes', 'array'],
            'competencies.*.competency_id' => ['required_with:competencies', 'integer', 'exists:performance_competencies,id'],
            'competencies.*.rating' => ['required_with:competencies', 'numeric', 'gt:0'],
            'competencies.*.comment' => ['nullable', 'string'],
            'overall_rating' => ['required', 'numeric', 'gt:0'],
            'summary' => ['required', 'string'],
            'confidential_notes' => ['nullable', 'string'],
        ];
    }
}
