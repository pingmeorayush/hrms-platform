<?php

namespace App\Modules\AttendanceManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateShiftAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'shift_id' => ['required', 'integer', Rule::exists('shifts', 'id')->where('company_id', $companyId)],
            'assignment_type' => ['required', Rule::in(['employee', 'department', 'location'])],
            'employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->where('company_id', $companyId)],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $assignmentType = $this->string('assignment_type')->toString();
            $payload = [
                'employee' => $this->filled('employee_id'),
                'department' => $this->filled('department_id'),
                'location' => $this->filled('location_id'),
            ];

            foreach ($payload as $type => $present) {
                if ($type === $assignmentType && ! $present) {
                    $validator->errors()->add("{$type}_id", ucfirst($type).' scope is required for this assignment type.');
                }

                if ($type !== $assignmentType && $present) {
                    $validator->errors()->add("{$type}_id", ucfirst($type).' scope is not allowed for this assignment type.');
                }
            }
        });
    }
}
