<?php

namespace App\Modules\Platform\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', Rule::unique('workflow_definitions', 'key')->where('company_id', $this->user()?->company_id)],
            'name' => ['required', 'string', 'max:255'],
            'module' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_template' => ['sometimes', 'boolean'],
            'publish' => ['sometimes', 'boolean'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.sequence' => ['required', 'integer', 'min:1'],
            'stages.*.approver_type' => ['required', Rule::in(['role', 'user'])],
            'stages.*.approver_value' => ['required'],
            'stages.*.available_actions' => ['sometimes', 'array', 'min:1'],
            'stages.*.available_actions.*' => ['string', Rule::in(['approve', 'reject', 'request_changes'])],
            'stages.*.sla_hours' => ['nullable', 'integer', 'min:1'],
            'stages.*.metadata' => ['sometimes', 'array'],
        ];
    }
}
