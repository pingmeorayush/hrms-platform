<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyEmployeeLifecycleTaskTemplatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_ids' => ['required', 'array', 'min:1'],
            'template_ids.*' => ['integer', 'distinct', 'exists:employee_lifecycle_task_templates,id'],
        ];
    }
}
