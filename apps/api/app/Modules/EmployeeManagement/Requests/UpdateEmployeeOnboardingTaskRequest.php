<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEmployeeOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lifecycle_type' => ['sometimes', Rule::in(['onboarding', 'offboarding'])],
            'title' => ['sometimes', 'string', 'max:150'],
            'category' => ['sometimes', Rule::in(['hr', 'it', 'manager', 'department', 'compliance', 'training', 'other'])],
            'task_type' => ['sometimes', 'nullable', Rule::in(['read_policy', 'submit_documents', 'complete_training', 'attend_session', 'meet_manager', 'setup_equipment', 'complete_forms', 'other'])],
            'assignee_type' => ['sometimes', Rule::in(['employee', 'manager', 'hr', 'it_team', 'facilities', 'security', 'other'])],
            'assigned_to_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'requires_approval' => ['sometimes', 'boolean'],
            'approval_workflow_key' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed', 'skipped'])],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:999'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->validated() === []) {
                    $validator->errors()->add('payload', 'At least one onboarding-task field must be provided.');
                }
            },
        ];
    }
}
