<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerformanceReviewCycleRequest extends FormRequest
{
    use AuthorizesPerformanceRequests;

    public function authorize(): bool
    {
        return $this->canManagePerformance();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'cycle_type' => ['required', 'string', Rule::in(['annual', 'half_yearly', 'quarterly', 'probation', 'project'])],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'self_review_due_on' => ['nullable', 'date', 'after_or_equal:starts_on', 'before_or_equal:ends_on'],
            'manager_review_due_on' => ['nullable', 'date', 'after_or_equal:starts_on', 'before_or_equal:ends_on'],
            'calibration_starts_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'calibration_ends_on' => ['nullable', 'date', 'after_or_equal:calibration_starts_on'],
            'publish_on' => ['nullable', 'date', 'after_or_equal:ends_on'],
            'participant_rules' => ['required', 'array'],
            'participant_rules.population' => ['required', 'array'],
            'participant_rules.population.employment_statuses' => ['sometimes', 'array'],
            'participant_rules.population.employment_types' => ['sometimes', 'array'],
            'participant_rules.population.department_ids' => ['sometimes', 'array'],
            'participant_rules.population.department_ids.*' => ['integer', 'exists:departments,id'],
            'participant_rules.population.designation_ids' => ['sometimes', 'array'],
            'participant_rules.population.designation_ids.*' => ['integer', 'exists:designations,id'],
            'participant_rules.reviewers' => ['required', 'array'],
            'participant_rules.reviewers.self_review_required' => ['required', 'boolean'],
            'participant_rules.reviewers.manager_review_required' => ['required', 'boolean'],
            'participant_rules.reviewers.peer_reviewer_slots' => ['nullable', 'integer', 'min:0', 'max:10'],
            'participant_rules.reviewers.allow_hr_reviewer' => ['required', 'boolean'],
            'review_template' => ['required', 'array'],
            'review_template.sections' => ['required', 'array', 'min:1'],
            'review_template.sections.*.key' => ['required', 'string', 'max:64'],
            'review_template.sections.*.label' => ['required', 'string', 'max:255'],
            'review_template.sections.*.weight_percent' => ['required', 'numeric', 'gt:0', 'lte:100'],
            'review_template.sections.*.required' => ['required', 'boolean'],
            'review_template.rating_scale' => ['required', 'array'],
            'review_template.rating_scale.min' => ['required', 'integer', 'min:1'],
            'review_template.rating_scale.max' => ['required', 'integer', 'gte:review_template.rating_scale.min'],
            'competency_visibility' => ['required', 'array'],
            'competency_visibility.enabled' => ['required', 'boolean'],
            'competency_visibility.visible_to_employee' => ['required', 'boolean'],
            'competency_visibility.visible_to_manager' => ['required', 'boolean'],
            'competency_visibility.visible_to_hr' => ['required', 'boolean'],
            'competency_visibility.required_competency_ids' => ['sometimes', 'array'],
            'competency_visibility.required_competency_ids.*' => ['integer', 'exists:performance_competencies,id'],
            'status' => ['required', 'string', Rule::in(['draft', 'scheduled', 'active', 'archived'])],
        ];
    }
}
