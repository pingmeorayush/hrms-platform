<?php

namespace App\Modules\AIAssistant\Requests;

use App\Modules\AIAssistant\Services\AiAssistantService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiChatRequest extends FormRequest
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
            'conversation_id' => ['nullable', 'integer', Rule::exists('ai_conversations', 'id')],
            'use_case' => ['nullable', Rule::in(AiAssistantService::supportedUseCaseKeys())],
            'subject_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'persona' => ['nullable', 'string', 'max:64'],
            'question' => ['required', 'string', 'max:4000'],
        ];
    }
}
