<?php

namespace App\Modules\Platform\Workflow\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workflow_definition_id' => ['required_without:workflow_key', 'integer', 'exists:workflow_definitions,id'],
            'workflow_key' => ['required_without:workflow_definition_id', 'string'],
            'reference_type' => ['required', 'string', 'max:100'],
            'reference_id' => ['required', 'string', 'max:100'],
            'payload' => ['sometimes', 'array'],
        ];
    }
}
