<?php

namespace App\Modules\AssetManagement\Requests;

use App\Models\AssetCategory;
use App\Models\Employee;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAssetRequest extends FormRequest
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
