<?php

namespace App\Modules\EmployeeManagement\Requests;

use App\Modules\EmployeeManagement\Requests\Concerns\AuthorizesEmployeeRequests;
use App\Modules\EmployeeManagement\Services\EmployeeCreationRules;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEmployeeRequest extends FormRequest
{
    use AuthorizesEmployeeRequests;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return app(EmployeeCreationRules::class)->rulesForCompany((int) $companyId);
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $companyId = $this->user()?->company_id;

                if (! $companyId) {
                    return;
                }

                app(EmployeeCreationRules::class)->applyCodePolicyValidation(
                    $validator,
                    (int) $companyId,
                    $this->all(),
                );
            },
        ];
    }
}
