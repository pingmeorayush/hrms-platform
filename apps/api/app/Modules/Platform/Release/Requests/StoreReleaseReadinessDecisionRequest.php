<?php

namespace App\Modules\Platform\Release\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReleaseReadinessDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('release.manage');
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'release_window_label' => ['required', 'string', 'max:120'],
            'target_environment' => ['required', 'string', Rule::in($this->targetEnvironments())],
            'decision_status' => ['required', Rule::in(['go', 'conditional', 'no_go'])],
            'summary' => ['required', 'string', 'max:255'],
            'blockers' => ['required_unless:decision_status,go', 'array'],
            'blockers.*.area_key' => ['nullable', 'string', 'max:64'],
            'blockers.*.title' => ['required_with:blockers', 'string', 'max:160'],
            'blockers.*.owner_role' => ['required_with:blockers', 'string', 'max:80'],
            'blockers.*.status' => ['nullable', Rule::in(['open', 'mitigated', 'accepted'])],
            'blockers.*.notes' => ['nullable', 'string', 'max:500'],
            'artifact_refs' => ['sometimes', 'array'],
            'artifact_refs.*' => ['string', 'max:255'],
            'decision_notes' => ['nullable', 'string', 'max:2000'],
            'decided_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function targetEnvironments(): array
    {
        return array_values(config('release_readiness.policy.target_environments', ['production']));
    }
}
