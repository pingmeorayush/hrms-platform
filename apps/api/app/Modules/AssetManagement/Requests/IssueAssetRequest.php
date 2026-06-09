<?php

namespace App\Modules\AssetManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'issued_at' => ['nullable', 'date'],
            'issue_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
