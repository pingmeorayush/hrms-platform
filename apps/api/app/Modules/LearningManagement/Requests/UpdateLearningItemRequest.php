<?php

namespace App\Modules\LearningManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLearningItemRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:64'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category' => ['sometimes', 'string', 'max:64'],
            'delivery_mode' => ['sometimes', 'string', Rule::in(['self_paced', 'instructor_led', 'virtual_session', 'blended', 'document_acknowledgement'])],
            'duration_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10000'],
            'requires_completion_evidence' => ['sometimes', 'boolean'],
            'renewal_frequency_months' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:60'],
            'default_due_days' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'status' => ['sometimes', 'string', Rule::in(['draft', 'active', 'archived'])],
        ];
    }
}
