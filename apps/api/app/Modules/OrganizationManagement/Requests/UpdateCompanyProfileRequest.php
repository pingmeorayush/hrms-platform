<?php

namespace App\Modules\OrganizationManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
