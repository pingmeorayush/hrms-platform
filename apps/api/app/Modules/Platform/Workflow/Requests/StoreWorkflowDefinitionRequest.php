<?php

namespace App\Modules\Platform\Workflow\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowDefinitionRequest extends FormRequest
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
            'stages.*.approver_type' => ['required', Rule::in(['role', 'user', 'employee_manager', 'payload_user'])],
            'stages.*.approver_value' => ['required'],
            'stages.*.available_actions' => ['sometimes', 'array', 'min:1'],
            'stages.*.available_actions.*' => ['string', Rule::in(['approve', 'reject', 'request_changes'])],
            'stages.*.sla_hours' => ['nullable', 'integer', 'min:1'],
            'stages.*.metadata' => ['sometimes', 'array'],
        ];
    }
}
