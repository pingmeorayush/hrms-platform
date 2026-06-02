<?php

namespace App\Modules\EmployeeManagement\Services;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EmployeeCreationRules
{
    public function __construct(private readonly EmployeeCodeService $employeeCodeService) {}

    public function rulesForCompany(int $companyId): array
    {
        return [
            'employee_code' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('employees', 'employee_code')->where('company_id', $companyId),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->where('company_id', $companyId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'non_binary', 'prefer_not_to_say'])],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'date_of_joining' => ['required', 'date'],
            'employment_type' => ['required', 'string', 'max:50'],
            'employment_status' => ['nullable', Rule::in(['active', 'inactive', 'probation', 'notice_period'])],
            'department_id' => ['required', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'designation_id' => ['required', Rule::exists('designations', 'id')->where('company_id', $companyId)],
            'manager_id' => ['nullable', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'location_id' => ['nullable', Rule::exists('locations', 'id')->where('company_id', $companyId)],
            'cost_center_id' => ['nullable', Rule::exists('cost_centers', 'id')->where('company_id', $companyId)],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('company_id', $companyId)],
        ];
    }

    public function applyCodePolicyValidation(Validator $validator, int $companyId, array $payload): void
    {
        $manualMode = $this->employeeCodeService->isManualMode($companyId);
        $hasCode = filled($payload['employee_code'] ?? null);

        if ($manualMode && ! $hasCode) {
            $validator->errors()->add(
                'employee_code',
                'Employee code is required when the tenant uses manual employee codes.',
            );
        }

        if (! $manualMode && $hasCode) {
            $validator->errors()->add(
                'employee_code',
                'Manual employee code entry is disabled for this tenant.',
            );
        }
    }
}
