<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPayrollAdjustmentsRequest extends FormRequest
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
                'sometimes',
                'integer',
                Rule::exists(Employee::class, 'id')->where('company_id', $companyId),
            ],
            'status' => [
                'sometimes',
                Rule::in(['active', 'cancelled']),
            ],
        ];
    }
}
