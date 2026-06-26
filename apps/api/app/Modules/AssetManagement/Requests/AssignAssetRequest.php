<?php

namespace App\Modules\AssetManagement\Requests;

use App\Models\Employee;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignAssetRequest extends FormRequest
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
