<?php

namespace App\Modules\EmployeeManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ValidateEmployeeBulkImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rows' => ['required_without:file', 'array', 'min:1'],
            'rows.*' => ['array'],
            'file' => ['required_without:rows', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->filled('rows') && $this->hasFile('file')) {
                    $validator->errors()->add(
                        'payload',
                        'Provide either rows or a CSV file, not both.',
                    );
                }
            },
        ];
    }
}
