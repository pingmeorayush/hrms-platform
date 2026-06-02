<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['email', 'phone', 'mobile', 'whatsapp', 'other'])],
            'label' => ['nullable', 'string', 'max:100'],
            'value' => ['required', 'string', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
