<?php

namespace App\Modules\DocumentManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreDocumentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:100'],
            'repository_scope' => ['required', Rule::in(config('document_repository.repository_scopes', []))],
            'default_visibility_scope' => ['required', Rule::in(config('document_repository.visibility_scopes', []))],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'allowed_role_names' => ['nullable', 'array'],
            'allowed_role_names.*' => [
                'string',
                'distinct',
                Rule::exists(Role::class, 'name')->where('guard_name', 'web'),
            ],
            'status' => ['required', Rule::in(config('document_repository.category_statuses', []))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
