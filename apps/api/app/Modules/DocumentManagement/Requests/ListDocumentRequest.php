<?php

namespace App\Modules\DocumentManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListDocumentRequest extends FormRequest
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
            'document_category_id' => ['sometimes', 'integer', 'min:1'],
            'repository_scope' => ['sometimes', Rule::in(config('document_repository.repository_scopes', []))],
            'linked_entity_type' => ['sometimes', 'string', 'max:100'],
            'linked_entity_id' => ['sometimes', 'integer', 'min:1'],
            'visibility_scope' => ['sometimes', Rule::in(config('document_repository.visibility_scopes', []))],
            'retention_until_from' => ['sometimes', 'date'],
            'retention_until_to' => ['sometimes', 'date', 'after_or_equal:retention_until_from'],
        ];
    }
}
