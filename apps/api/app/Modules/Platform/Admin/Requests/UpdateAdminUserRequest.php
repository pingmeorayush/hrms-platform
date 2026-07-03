<?php

namespace App\Modules\Platform\Admin\Requests;

use App\Models\User;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminUserRequest extends FormRequest
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
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(12)->mixedCase()->letters()->numbers()->symbols(),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'requires_mfa' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
