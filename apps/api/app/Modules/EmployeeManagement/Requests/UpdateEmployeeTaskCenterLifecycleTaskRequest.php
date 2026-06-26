<?php

namespace App\Modules\EmployeeManagement\Requests;

use App\Modules\EmployeeManagement\Requests\Concerns\AuthorizesEmployeeRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeTaskCenterLifecycleTaskRequest extends FormRequest
{
    use AuthorizesEmployeeRequests;

    public function authorize(): bool
    {
        return $this->employeeRequestUser() !== null;
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['in_progress', 'completed'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
