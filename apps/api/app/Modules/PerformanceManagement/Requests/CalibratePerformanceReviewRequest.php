<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CalibratePerformanceReviewRequest extends FormRequest
{
    use AuthorizesPerformanceRequests;

    public function authorize(): bool
    {
        return $this->canAdministerPerformance();
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'overall_rating' => ['required', 'numeric', 'gt:0'],
            'summary' => ['required', 'string'],
            'confidential_notes' => ['nullable', 'string'],
            'section_adjustments' => ['sometimes', 'array'],
            'section_adjustments.*.key' => ['required_with:section_adjustments', 'string', 'max:64'],
            'section_adjustments.*.calibrated_rating' => ['required_with:section_adjustments', 'numeric', 'gt:0'],
            'section_adjustments.*.note' => ['nullable', 'string'],
            'competency_adjustments' => ['sometimes', 'array'],
            'competency_adjustments.*.competency_id' => ['required_with:competency_adjustments', 'integer', 'exists:performance_competencies,id'],
            'competency_adjustments.*.calibrated_rating' => ['required_with:competency_adjustments', 'numeric', 'gt:0'],
            'competency_adjustments.*.note' => ['nullable', 'string'],
        ];
    }
}
