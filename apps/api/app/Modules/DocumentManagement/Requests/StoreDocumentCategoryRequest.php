<?php

namespace App\Modules\DocumentManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreDocumentCategoryRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
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
