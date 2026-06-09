<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePolicyAcknowledgementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_id' => ['required', 'integer', 'exists:documents,id'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:employees,id'],
            'policy_version' => ['nullable', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
            'assignment_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
