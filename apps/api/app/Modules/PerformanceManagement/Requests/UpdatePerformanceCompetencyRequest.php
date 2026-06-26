<?php

namespace App\Modules\PerformanceManagement\Requests;

use App\Modules\PerformanceManagement\Requests\Concerns\AuthorizesPerformanceRequests;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerformanceCompetencyRequest extends FormRequest
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
            'code' => ['sometimes', 'string', 'max:64'],
            'name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:64'],
            'description' => ['nullable', 'string'],
            'scale_definition' => ['sometimes', 'array'],
            'scale_definition.min_rating' => ['required_with:scale_definition', 'integer', 'min:1'],
            'scale_definition.max_rating' => ['required_with:scale_definition', 'integer', 'gte:scale_definition.min_rating'],
            'scale_definition.labels' => ['required_with:scale_definition', 'array', 'min:1'],
            'scale_definition.labels.*.value' => ['required_with:scale_definition', 'integer'],
            'scale_definition.labels.*.label' => ['required_with:scale_definition', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'archived'])],
        ];
    }
}
