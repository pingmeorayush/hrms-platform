<?php

namespace App\Modules\DocumentManagement\Requests;

use App\Models\DocumentCategory;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDocumentRequest extends FormRequest
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
        $allowedExtensions = implode(',', config('document_repository.allowed_extensions', []));
        $maxFileSizeKb = (int) config('document_repository.max_file_size_kb', 15360);

        return [
            'title' => ['required', 'string', 'max:150'],
            'document_category_id' => [
                'nullable',
                'integer',
                Rule::exists(DocumentCategory::class, 'id')->where('company_id', $this->user()?->company_id),
            ],
            'repository_scope' => ['nullable', Rule::in(config('document_repository.repository_scopes', []))],
            'linked_entity_type' => ['nullable', 'string', 'max:100'],
            'linked_entity_id' => ['nullable', 'integer', 'min:1'],
            'visibility_scope' => ['nullable', Rule::in(config('document_repository.visibility_scopes', []))],
            'retention_until' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimes:'.$allowedExtensions, 'max:'.$maxFileSizeKb],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $hasCategory = $this->filled('document_category_id');

                if (! $hasCategory && ! $this->filled('repository_scope')) {
                    $validator->errors()->add('repository_scope', 'A repository scope is required when no document category is selected.');
                }

                if (! $hasCategory && ! $this->filled('visibility_scope')) {
                    $validator->errors()->add('visibility_scope', 'A visibility scope is required when no document category is selected.');
                }
            },
        ];
    }
}
