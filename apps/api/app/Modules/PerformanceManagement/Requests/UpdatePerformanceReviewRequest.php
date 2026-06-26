<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePerformanceReviewRequest extends FormRequest
{
    use AuthorizesPerformanceRequests;

    public function authorize(): bool
    {
        return $this->canManagePerformance();
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'reviewer_user_ids' => ['sometimes', 'array'],
            'reviewer_user_ids.*' => ['integer', 'exists:users,id'],
            'visibility_rules' => ['sometimes', 'array'],
            'visibility_rules.employee_can_view_manager_assessment_before_publish' => ['sometimes', 'boolean'],
            'visibility_rules.employee_can_view_peer_feedback_after_publish' => ['sometimes', 'boolean'],
            'visibility_rules.peer_feedback_anonymous_to_employee' => ['sometimes', 'boolean'],
            'visibility_rules.manager_can_view_peer_feedback' => ['sometimes', 'boolean'],
            'visibility_rules.reviewer_can_view_other_reviewer_feedback' => ['sometimes', 'boolean'],
        ];
    }
}
