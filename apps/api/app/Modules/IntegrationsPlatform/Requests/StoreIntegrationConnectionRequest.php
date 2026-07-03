<?php

namespace App\Modules\IntegrationsPlatform\Requests;

use App\Modules\IntegrationsPlatform\Requests\Concerns\AuthorizesIntegrationRequests;
use App\Modules\IntegrationsPlatform\Requests\Concerns\HasIntegrationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIntegrationConnectionRequest extends FormRequest
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
            'system_key' => ['required', Rule::in($this->systemKeys())],
            'name' => ['required', 'string', 'max:120'],
            'version' => ['sometimes', 'string', 'max:16'],
            'direction' => ['required', Rule::in($this->connectionDirections())],
            'status' => ['sometimes', Rule::in($this->connectionStatuses())],
            'auth_mode' => ['sometimes', Rule::in($this->authModes())],
            'endpoint_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string'],
            'scopes' => ['sometimes', 'array'],
            'scopes.*' => ['string', 'max:100'],
            'settings' => ['sometimes', 'array'],
        ];
    }
}
