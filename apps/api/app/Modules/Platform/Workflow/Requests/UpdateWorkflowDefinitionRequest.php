<?php

namespace App\Modules\Platform\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkflowDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['publish', 'new_version', 'archive'])],
            'version' => ['sometimes', 'integer', 'min:1'],
            'publish' => ['sometimes', 'boolean'],
            'stages' => ['sometimes', 'array', 'min:1'],
            'stages.*.key' => ['required_with:stages', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'stages.*.name' => ['required_with:stages', 'string', 'max:255'],
            'stages.*.sequence' => ['required_with:stages', 'integer', 'min:1'],
            'stages.*.approver_type' => ['required_with:stages', Rule::in(['role', 'user'])],
            'stages.*.approver_value' => ['required_with:stages'],
            'stages.*.available_actions' => ['sometimes', 'array', 'min:1'],
            'stages.*.available_actions.*' => ['string', Rule::in(['approve', 'reject', 'request_changes'])],
            'stages.*.sla_hours' => ['nullable', 'integer', 'min:1'],
            'stages.*.metadata' => ['sometimes', 'array'],
        ];
    }
}
