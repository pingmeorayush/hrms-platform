<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(['hr', 'it', 'manager', 'department', 'compliance', 'training', 'other'])],
            'task_type' => ['nullable', Rule::in(['read_policy', 'submit_documents', 'complete_training', 'attend_session', 'meet_manager', 'setup_equipment', 'complete_forms', 'other'])],
            'assignee_type' => ['required', Rule::in(['employee', 'manager', 'hr', 'it_team', 'facilities', 'security', 'other'])],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'skipped'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
