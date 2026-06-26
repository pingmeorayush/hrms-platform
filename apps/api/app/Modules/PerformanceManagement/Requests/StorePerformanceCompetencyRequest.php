<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerformanceCompetencyRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
            'scale_definition' => ['required', 'array'],
            'scale_definition.min_rating' => ['required', 'integer', 'min:1'],
            'scale_definition.max_rating' => ['required', 'integer', 'gte:scale_definition.min_rating'],
            'scale_definition.labels' => ['required', 'array', 'min:1'],
            'scale_definition.labels.*.value' => ['required', 'integer'],
            'scale_definition.labels.*.label' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'archived'])],
        ];
    }
}
