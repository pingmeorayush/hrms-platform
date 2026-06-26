<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerformanceReviewCycleRequest extends FormRequest
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
            'code' => ['sometimes', 'string', 'max:64'],
            'name' => ['sometimes', 'string', 'max:255'],
            'cycle_type' => ['sometimes', 'string', Rule::in(['annual', 'half_yearly', 'quarterly', 'probation', 'project'])],
            'starts_on' => ['sometimes', 'date'],
            'ends_on' => ['sometimes', 'date', 'after_or_equal:starts_on'],
            'self_review_due_on' => ['nullable', 'date'],
            'manager_review_due_on' => ['nullable', 'date'],
            'calibration_starts_on' => ['nullable', 'date'],
            'calibration_ends_on' => ['nullable', 'date', 'after_or_equal:calibration_starts_on'],
            'publish_on' => ['nullable', 'date'],
            'participant_rules' => ['sometimes', 'array'],
            'participant_rules.population' => ['required_with:participant_rules', 'array'],
            'participant_rules.population.employment_statuses' => ['sometimes', 'array'],
            'participant_rules.population.employment_types' => ['sometimes', 'array'],
            'participant_rules.population.department_ids' => ['sometimes', 'array'],
            'participant_rules.population.department_ids.*' => ['integer', 'exists:departments,id'],
            'participant_rules.population.designation_ids' => ['sometimes', 'array'],
            'participant_rules.population.designation_ids.*' => ['integer', 'exists:designations,id'],
            'participant_rules.reviewers' => ['required_with:participant_rules', 'array'],
            'participant_rules.reviewers.self_review_required' => ['required_with:participant_rules', 'boolean'],
            'participant_rules.reviewers.manager_review_required' => ['required_with:participant_rules', 'boolean'],
            'participant_rules.reviewers.peer_reviewer_slots' => ['nullable', 'integer', 'min:0', 'max:10'],
            'participant_rules.reviewers.allow_hr_reviewer' => ['required_with:participant_rules', 'boolean'],
            'review_template' => ['sometimes', 'array'],
            'review_template.sections' => ['required_with:review_template', 'array', 'min:1'],
            'review_template.sections.*.key' => ['required_with:review_template', 'string', 'max:64'],
            'review_template.sections.*.label' => ['required_with:review_template', 'string', 'max:255'],
            'review_template.sections.*.weight_percent' => ['required_with:review_template', 'numeric', 'gt:0', 'lte:100'],
            'review_template.sections.*.required' => ['required_with:review_template', 'boolean'],
            'review_template.rating_scale' => ['required_with:review_template', 'array'],
            'review_template.rating_scale.min' => ['required_with:review_template', 'integer', 'min:1'],
            'review_template.rating_scale.max' => ['required_with:review_template', 'integer', 'gte:review_template.rating_scale.min'],
            'competency_visibility' => ['sometimes', 'array'],
            'competency_visibility.enabled' => ['required_with:competency_visibility', 'boolean'],
            'competency_visibility.visible_to_employee' => ['required_with:competency_visibility', 'boolean'],
            'competency_visibility.visible_to_manager' => ['required_with:competency_visibility', 'boolean'],
            'competency_visibility.visible_to_hr' => ['required_with:competency_visibility', 'boolean'],
            'competency_visibility.required_competency_ids' => ['sometimes', 'array'],
            'competency_visibility.required_competency_ids.*' => ['integer', 'exists:performance_competencies,id'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'scheduled', 'active', 'archived'])],
        ];
    }
}
