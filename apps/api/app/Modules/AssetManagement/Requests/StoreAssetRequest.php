<?php

namespace App\Modules\AssetManagement\Requests;

use App\Models\AssetCategory;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
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
        $companyId = $this->user()?->company_id;

        return [
            'asset_category_id' => [
                'required',
                'integer',
                Rule::exists(AssetCategory::class, 'id')->where('company_id', $companyId),
            ],
            'asset_tag' => ['required', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:150'],
            'asset_type' => ['required', Rule::in(['physical', 'digital', 'accessory', 'license'])],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'model_name' => ['nullable', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::in(['available', 'maintenance', 'retired'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
