<?php

namespace App\Modules\AIAssistant\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiRecommendationDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('ai.recommend');
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['accepted', 'rejected'])],
            'decision_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
