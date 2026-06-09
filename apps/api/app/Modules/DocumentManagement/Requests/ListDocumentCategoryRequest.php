<?php

namespace App\Modules\DocumentManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListDocumentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'repository_scope' => ['sometimes', Rule::in(config('document_repository.repository_scopes', []))],
            'status' => ['sometimes', Rule::in(config('document_repository.category_statuses', []))],
        ];
    }
}
