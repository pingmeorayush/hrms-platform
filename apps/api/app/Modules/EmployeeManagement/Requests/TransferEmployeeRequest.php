<?php

namespace App\Modules\EmployeeManagement\Requests;

use App\Modules\EmployeeManagement\Requests\Concerns\AuthorizesEmployeeRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransferEmployeeRequest extends FormRequest
{
    use AuthorizesEmployeeRequests;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'effective_date' => ['required', 'date'],
            'department_id' => ['sometimes', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'manager_id' => ['sometimes', 'nullable', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'location_id' => ['sometimes', 'nullable', Rule::exists('locations', 'id')->where('company_id', $companyId)],
            'cost_center_id' => ['sometimes', 'nullable', Rule::exists('cost_centers', 'id')->where('company_id', $companyId)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'designation_id' => ['prohibited'],
            'termination_date' => ['prohibited'],
            'reason' => ['prohibited'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $fields = ['department_id', 'manager_id', 'location_id', 'cost_center_id'];

                if (! collect($fields)->contains(fn (string $field): bool => $this->exists($field))) {
                    $validator->errors()->add(
                        'payload',
                        'At least one transfer field must be provided.',
                    );
                }
            },
        ];
    }
}
