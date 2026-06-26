<?php

namespace App\Modules\EmployeeManagement\Requests;

use App\Modules\EmployeeManagement\Requests\Concerns\AuthorizesEmployeeRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeLifecycleTaskTemplateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'lifecycle_type' => ['required', Rule::in(['onboarding', 'offboarding'])],
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(['hr', 'it', 'manager', 'department', 'compliance', 'training', 'other'])],
            'task_type' => ['nullable', Rule::in(['read_policy', 'submit_documents', 'complete_training', 'attend_session', 'meet_manager', 'setup_equipment', 'complete_forms', 'other'])],
            'assignee_type' => ['required', Rule::in(['employee', 'manager', 'hr', 'it_team', 'facilities', 'security', 'other'])],
            'requires_approval' => ['nullable', 'boolean'],
            'approval_workflow_key' => ['nullable', 'string', 'max:100'],
            'due_offset_days' => ['nullable', 'integer', 'min:-30', 'max:365'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
