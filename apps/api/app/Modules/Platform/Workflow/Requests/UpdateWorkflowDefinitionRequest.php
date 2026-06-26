<?php

namespace App\Modules\Platform\Workflow\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkflowDefinitionRequest extends FormRequest
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
            'action' => ['required', Rule::in(['publish', 'new_version', 'archive'])],
            'version' => ['sometimes', 'integer', 'min:1'],
            'publish' => ['sometimes', 'boolean'],
            'stages' => ['sometimes', 'array', 'min:1'],
            'stages.*.key' => ['required_with:stages', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'stages.*.name' => ['required_with:stages', 'string', 'max:255'],
            'stages.*.sequence' => ['required_with:stages', 'integer', 'min:1'],
            'stages.*.approver_type' => ['required_with:stages', Rule::in(['role', 'user', 'employee_manager', 'payload_user'])],
            'stages.*.approver_value' => ['required_with:stages'],
            'stages.*.available_actions' => ['sometimes', 'array', 'min:1'],
            'stages.*.available_actions.*' => ['string', Rule::in(['approve', 'reject', 'request_changes'])],
            'stages.*.sla_hours' => ['nullable', 'integer', 'min:1'],
            'stages.*.metadata' => ['sometimes', 'array'],
        ];
    }
}
