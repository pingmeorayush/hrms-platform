<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\Employee;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListEmployeeCompensationsRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    protected function prepareForValidation(): void
    {
        if (! $this->has('current_only')) {
            $this->merge(['current_only' => true]);

            return;
        }

        $this->merge([
            'current_only' => $this->boolean('current_only'),
        ]);
    }

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
                'nullable',
                'integer',
                Rule::exists(Employee::class, 'id')->where('company_id', $companyId),
            ],
            'current_only' => ['sometimes', 'boolean'],
        ];
    }
}
