<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerformanceGoalRequest extends FormRequest
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
            'goal_code' => ['sometimes', 'string', 'max:64'],
            'goal_type' => ['sometimes', 'string', Rule::in(['library'])],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'performance_review_cycle_id' => ['nullable', 'integer', 'exists:performance_review_cycles,id'],
            'owner_employee_id' => ['sometimes', 'integer', 'exists:employees,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'due_on' => ['sometimes', 'date'],
            'weight_percent' => ['sometimes', 'numeric', 'gt:0', 'lte:100'],
            'success_metric' => ['nullable', 'array'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'active', 'archived'])],
        ];
    }
}
