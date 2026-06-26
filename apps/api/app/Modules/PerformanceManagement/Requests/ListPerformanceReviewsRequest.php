<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPerformanceReviewsRequest extends FormRequest
{
    use AuthorizesPerformanceRequests;

    public function authorize(): bool
    {
        return $this->canAccessPerformanceWorkspace();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['draft', 'self_assessment', 'manager_review', 'calibration', 'finalized', 'published', 'reopened'])],
            'review_cycle_id' => ['sometimes', 'integer', 'exists:performance_review_cycles,id'],
            'employee_id' => ['sometimes', 'integer', 'exists:employees,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
