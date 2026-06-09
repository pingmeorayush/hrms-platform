<?php

namespace App\Modules\AssetManagement\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'employee_id' => [
                'required',
                'integer',
                Rule::exists(Employee::class, 'id')->where('company_id', $companyId),
            ],
            'assigned_at' => ['nullable', 'date'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:assigned_at'],
            'handover_condition' => ['nullable', 'string', 'max:150'],
            'assignment_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
