<?php

namespace App\Modules\Platform\Admin\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAdminUserRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->letters()->numbers()->symbols(),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'requires_mfa' => ['sometimes', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
