<?php

namespace App\Modules\LearningManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteLearningAssignmentTargetRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'completion_notes' => ['nullable', 'string'],
            'completion_evidence' => ['nullable', 'array'],
            'completion_evidence.type' => ['required_with:completion_evidence', 'string', 'max:64'],
            'completion_evidence.reference' => ['required_with:completion_evidence', 'string', 'max:255'],
            'completion_evidence.notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
