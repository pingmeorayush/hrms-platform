<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedExtensions = implode(',', config('employee_documents.allowed_extensions', []));
        $maxFileSizeKb = (int) config('employee_documents.max_file_size_kb', 10240);

        return [
            'document_type' => ['required', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimes:'.$allowedExtensions, 'max:'.$maxFileSizeKb],
        ];
    }
}
