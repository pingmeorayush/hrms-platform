<?php

namespace App\Modules\OrganizationManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'subscription_plan' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'timezone:all'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['USD', 'INR', 'EUR', 'GBP', 'AED', 'SGD'])],
        ];
    }
}
