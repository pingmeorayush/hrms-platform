<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerformanceGoalRequest extends FormRequest
{
    use AuthorizesPerformanceRequests;

    public function authorize(): bool
    {
        return $this->canManagePerformance();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'goal_code' => ['required', 'string', 'max:64'],
            'goal_type' => ['required', 'string', Rule::in(['library'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'performance_review_cycle_id' => ['nullable', 'integer', 'exists:performance_review_cycles,id'],
            'owner_employee_id' => ['required', 'integer', 'exists:employees,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'due_on' => ['required', 'date'],
            'weight_percent' => ['required', 'numeric', 'gt:0', 'lte:100'],
            'success_metric' => ['nullable', 'array'],
            'status' => ['required', 'string', Rule::in(['draft', 'active', 'archived'])],
        ];
    }
}
