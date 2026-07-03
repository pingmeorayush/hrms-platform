<?php

namespace App\Modules\IntegrationsPlatform\Requests;

use App\Modules\IntegrationsPlatform\Requests\Concerns\AuthorizesIntegrationRequests;
use App\Modules\IntegrationsPlatform\Requests\Concerns\HasIntegrationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebhookSubscriptionRequest extends FormRequest
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
            'integration_connection_id' => ['required', 'integer', 'exists:integration_connections,id'],
            'event_key' => ['required', Rule::in($this->eventKeys())],
            'version' => ['sometimes', 'string', 'max:16'],
            'direction' => ['required', Rule::in($this->subscriptionDirections())],
            'status' => ['sometimes', Rule::in($this->subscriptionStatuses())],
            'endpoint_url' => ['nullable', 'url', 'max:2048', 'required_if:direction,outbound'],
            'secret' => ['required', 'string', 'min:12', 'max:255'],
            'custom_headers' => ['sometimes', 'array'],
            'custom_headers.*' => ['string', 'max:500'],
            'filter_rules' => ['sometimes', 'array'],
        ];
    }
}
