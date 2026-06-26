<?php

namespace App\Modules\LearningManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLearningItemRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:64'],
            'delivery_mode' => ['required', 'string', Rule::in(['self_paced', 'instructor_led', 'virtual_session', 'blended', 'document_acknowledgement'])],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'requires_completion_evidence' => ['required', 'boolean'],
            'renewal_frequency_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'default_due_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'metadata' => ['nullable', 'array'],
            'status' => ['required', 'string', Rule::in(['draft', 'active', 'archived'])],
        ];
    }
}
