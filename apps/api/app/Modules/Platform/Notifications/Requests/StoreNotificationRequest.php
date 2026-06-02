<?php

namespace App\Modules\Platform\Notifications\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'template_key' => ['nullable', 'string'],
            'title' => ['required_without:template_key', 'string', 'max:255'],
            'message' => ['required_without:template_key', 'string'],
            'type' => ['sometimes', 'string', 'max:100'],
            'channel' => ['sometimes', Rule::in(['in_app'])],
            'priority' => ['sometimes', Rule::in(['low', 'normal', 'high'])],
            'deep_link' => ['nullable', 'string', 'max:255'],
            'data' => ['sometimes', 'array'],
            'variables' => ['sometimes', 'array'],
        ];
    }
}
