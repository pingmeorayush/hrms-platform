<?php

namespace App\Modules\AIAssistant\Requests;

use App\Modules\AIAssistant\Services\AiAssistantService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiRecommendationRequest extends FormRequest
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
            'conversation_id' => ['nullable', 'integer', Rule::exists('ai_conversations', 'id')],
            'scenario' => ['required', Rule::in(AiAssistantService::approvedRecommendationScenarioKeys())],
            'subject_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'context_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
