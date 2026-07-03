<?php

namespace App\Modules\IntegrationsPlatform\Requests;

use App\Modules\IntegrationsPlatform\Requests\Concerns\AuthorizesIntegrationRequests;
use App\Modules\IntegrationsPlatform\Requests\Concerns\HasIntegrationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListWebhookSubscriptionsRequest extends FormRequest
{
    use AuthorizesIntegrationRequests;
    use HasIntegrationRules;

    public function authorize(): bool
    {
        return $this->canViewIntegrations();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'integration_connection_id' => ['sometimes', 'integer', 'min:1'],
            'event_key' => ['sometimes', Rule::in($this->eventKeys())],
            'status' => ['sometimes', Rule::in($this->subscriptionStatuses())],
            'direction' => ['sometimes', Rule::in($this->subscriptionDirections())],
        ];
    }
}
