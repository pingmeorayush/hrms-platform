<?php

namespace App\Modules\EmployeeManagement\Requests;

use App\Modules\EmployeeManagement\Requests\Concerns\AuthorizesEmployeeRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeOnboardingTaskRequest extends FormRequest
{
    use AuthorizesEmployeeRequests;

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
            'template_id' => ['nullable', 'integer', 'exists:employee_lifecycle_task_templates,id'],
            'lifecycle_type' => ['sometimes', Rule::in(['onboarding', 'offboarding'])],
            'title' => ['required_without:template_id', 'string', 'max:150'],
            'category' => ['required_without:template_id', Rule::in(['hr', 'it', 'manager', 'department', 'compliance', 'training', 'other'])],
            'task_type' => ['nullable', Rule::in(['read_policy', 'submit_documents', 'complete_training', 'attend_session', 'meet_manager', 'setup_equipment', 'complete_forms', 'other'])],
            'assignee_type' => ['required_without:template_id', Rule::in(['employee', 'manager', 'hr', 'it_team', 'facilities', 'security', 'other'])],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'requires_approval' => ['nullable', 'boolean'],
            'approval_workflow_key' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'skipped'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
