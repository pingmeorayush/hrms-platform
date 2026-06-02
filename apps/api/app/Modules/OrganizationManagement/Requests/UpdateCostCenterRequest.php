<?php

namespace App\Modules\OrganizationManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;
        $costCenterId = (int) $this->route('costCenterId');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('cost_centers', 'code')->ignore($costCenterId)->where('company_id', $companyId),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('cost_centers', 'name')->ignore($costCenterId)->where('company_id', $companyId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
