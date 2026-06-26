<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PayrollRunActionRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'string', 'max:500'],
            'comment' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
