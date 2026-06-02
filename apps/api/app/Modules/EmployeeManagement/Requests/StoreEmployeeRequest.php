<?php

namespace App\Modules\EmployeeManagement\Requests;

use App\Modules\EmployeeManagement\Services\EmployeeCreationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return app(EmployeeCreationRules::class)->rulesForCompany((int) $companyId);
    }

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
