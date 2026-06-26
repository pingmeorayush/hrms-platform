<?php

namespace App\Modules\LearningManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLearningAssignmentRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'learning_item_id' => ['required', 'integer', 'exists:learning_items,id'],
            'audience_type' => ['required', 'string', Rule::in(['employee', 'department', 'designation', 'all_active'])],
            'audience_rules' => ['required', 'array'],
            'audience_rules.employee_ids' => ['sometimes', 'array', 'min:1'],
            'audience_rules.employee_ids.*' => ['integer', 'exists:employees,id'],
            'audience_rules.department_ids' => ['sometimes', 'array', 'min:1'],
            'audience_rules.department_ids.*' => ['integer', 'exists:departments,id'],
            'audience_rules.designation_ids' => ['sometimes', 'array', 'min:1'],
            'audience_rules.designation_ids.*' => ['integer', 'exists:designations,id'],
            'assigned_on' => ['nullable', 'date'],
            'due_on' => ['nullable', 'date'],
            'requires_completion_evidence' => ['nullable', 'boolean'],
            'renewal_frequency_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'default_due_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
