<?php

namespace App\Modules\LeaveManagement\Requests;

use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListLeaveBalancesRequest extends FormRequest
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
                'nullable',
                'integer',
                Rule::exists(Employee::class, 'id')->where('company_id', $companyId),
            ],
            'leave_type_id' => [
                'nullable',
                'integer',
                Rule::exists(LeaveType::class, 'id')->where('company_id', $companyId),
            ],
        ];
    }
}
