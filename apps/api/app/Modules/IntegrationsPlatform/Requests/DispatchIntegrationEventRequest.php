<?php

namespace App\Modules\IntegrationsPlatform\Requests;

use App\Modules\IntegrationsPlatform\Requests\Concerns\AuthorizesIntegrationRequests;
use App\Modules\IntegrationsPlatform\Requests\Concerns\HasIntegrationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DispatchIntegrationEventRequest extends FormRequest
{
    use AuthorizesIntegrationRequests;
    use HasIntegrationRules;

    public function authorize(): bool
    {
        return $this->canManageIntegrations();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'event_key' => ['required', Rule::in($this->eventKeys())],
            'entity_type' => ['nullable', 'string', 'max:64'],
            'entity_id' => ['nullable', 'string', 'max:64'],
            'payload' => ['required', 'array'],
            'subscription_ids' => ['sometimes', 'array'],
            'subscription_ids.*' => ['integer', 'min:1'],
            'process_now' => ['sometimes', 'boolean'],
        ];
    }
}
