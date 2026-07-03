<?php

namespace App\Modules\IntegrationsPlatform\Requests;

use App\Modules\IntegrationsPlatform\Requests\Concerns\AuthorizesIntegrationRequests;
use App\Modules\IntegrationsPlatform\Requests\Concerns\HasIntegrationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListIntegrationSyncJobsRequest extends FormRequest
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
            'status' => ['sometimes', Rule::in($this->syncJobStatuses())],
            'event_key' => ['sometimes', Rule::in($this->eventKeys())],
            'integration_connection_id' => ['sometimes', 'integer', 'min:1'],
            'webhook_subscription_id' => ['sometimes', 'integer', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
