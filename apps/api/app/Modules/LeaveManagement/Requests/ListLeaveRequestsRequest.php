<?php

namespace App\Modules\LeaveManagement\Requests;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListLeaveRequestsRequest extends FormRequest
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
            'employee_id' => ['sometimes', 'integer', Rule::exists(Employee::class, 'id')->where('company_id', $companyId)],
            'leave_type_id' => ['sometimes', 'integer', Rule::exists(LeaveType::class, 'id')->where('company_id', $companyId)],
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected', 'cancelled', 'changes_requested'])],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
