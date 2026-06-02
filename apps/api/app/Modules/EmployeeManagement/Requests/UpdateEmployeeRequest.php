<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;
        $employeeId = (int) $this->route('employeeId');

        return [
            'employee_code' => ['prohibited'],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employeeId)->where('company_id', $companyId),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female', 'non_binary', 'prefer_not_to_say'])],
            'marital_status' => ['sometimes', 'nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'employment_type' => ['sometimes', 'string', 'max:50'],
            'user_id' => ['sometimes', 'nullable', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'department_id' => ['prohibited'],
            'designation_id' => ['prohibited'],
            'manager_id' => ['prohibited'],
            'location_id' => ['prohibited'],
            'cost_center_id' => ['prohibited'],
            'termination_reason' => ['prohibited'],
            'terminated_at' => ['prohibited'],
            'employment_status' => ['prohibited'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $allowedFields = [
                    'first_name',
                    'middle_name',
                    'last_name',
                    'email',
                    'phone',
                    'date_of_birth',
                    'gender',
                    'marital_status',
                    'employment_type',
                    'user_id',
                ];

                $provided = collect($allowedFields)
                    ->filter(fn (string $field): bool => $this->exists($field))
                    ->values();

                if ($provided->isEmpty()) {
                    $validator->errors()->add(
                        'payload',
                        'At least one editable employee field must be provided.',
                    );
                }
            },
        ];
    }
}
