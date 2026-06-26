<?php

namespace App\Modules\Platform\Auth\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyMfaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'min:6', 'max:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
