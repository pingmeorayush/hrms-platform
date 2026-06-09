<?php

namespace App\Modules\AssetManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'returned_at' => ['nullable', 'date'],
            'return_condition' => ['nullable', 'string', 'max:150'],
            'return_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
