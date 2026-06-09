<?php

namespace App\Modules\AssetManagement\Requests;

use App\Models\AssetCategory;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'status' => ['sometimes', Rule::in(['available', 'assigned', 'issued', 'returned', 'maintenance', 'retired'])],
            'asset_category_id' => [
                'sometimes',
                'integer',
                Rule::exists(AssetCategory::class, 'id')->where('company_id', $companyId),
            ],
            'employee_id' => [
                'sometimes',
                'integer',
                Rule::exists(Employee::class, 'id')->where('company_id', $companyId),
            ],
        ];
    }
}
