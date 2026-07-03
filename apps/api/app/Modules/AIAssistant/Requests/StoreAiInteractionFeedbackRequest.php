<?php

namespace App\Modules\AIAssistant\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiInteractionFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('ai.view') || (bool) $this->user()?->can('ai.recommend');
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'sentiment' => ['required', Rule::in(['positive', 'negative', 'neutral'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
