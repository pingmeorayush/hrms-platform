<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListEmployeeCompensationsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('current_only')) {
            $this->merge(['current_only' => true]);

            return;
        }

        $this->merge([
            'current_only' => $this->boolean('current_only'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'employee_id' => [
                'nullable',
                'integer',
                Rule::exists(Employee::class, 'id')->where('company_id', $companyId),
            ],
            'current_only' => ['sometimes', 'boolean'],
        ];
    }
}
