<?php

namespace App\Modules\Platform\Resilience\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResilienceValidationRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('resilience.manage');
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'scenario_key' => ['required', 'string', Rule::in($this->scenarioKeys())],
            'status' => ['required', Rule::in(['passed', 'issues_found', 'failed', 'in_progress'])],
            'recovery_point_actual_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'recovery_time_actual_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'evidence_refs' => ['required_unless:status,in_progress', 'array', 'min:1'],
            'evidence_refs.*' => ['string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function scenarioKeys(): array
    {
        return array_keys(config('resilience.scenarios', []));
    }
}
