<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEmployeeContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::in(['email', 'phone', 'mobile', 'whatsapp', 'other'])],
            'label' => ['sometimes', 'nullable', 'string', 'max:100'],
            'value' => ['sometimes', 'string', 'max:255'],
            'is_primary' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->validated() === []) {
                    $validator->errors()->add('payload', 'At least one contact field must be provided.');
                }
            },
        ];
    }
}
