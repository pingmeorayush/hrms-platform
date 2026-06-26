<?php

namespace App\Modules\LeaveManagement\Requests;

use App\Models\Department;
use App\Models\LeaveType;
use App\Models\Location;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeavePolicyRequest extends FormRequest
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
            'leave_type_id' => [
                'required',
                'integer',
                Rule::exists(LeaveType::class, 'id')->where('company_id', $companyId),
            ],
            'annual_allowance_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'opening_balance_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'accrual_frequency' => ['required', Rule::in(['monthly', 'quarterly', 'annual', 'none'])],
            'carry_forward_limit_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'encashment_limit_days' => ['required', 'numeric', 'min:0', 'max:365'],
            'max_consecutive_days' => ['required', 'numeric', 'min:0.5', 'max:365'],
            'min_notice_days' => ['required', 'integer', 'min:0', 'max:365'],
            'requires_documentation_after_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'applicable_department_id' => [
                'nullable',
                'integer',
                Rule::exists(Department::class, 'id')->where('company_id', $companyId),
            ],
            'applicable_location_id' => [
                'nullable',
                'integer',
                Rule::exists(Location::class, 'id')->where('company_id', $companyId),
            ],
            'eligibility_rule' => ['nullable', 'array'],
            'eligibility_rule.employment_types' => ['sometimes', 'array'],
            'eligibility_rule.employment_types.*' => ['string', 'max:50', 'distinct'],
            'eligibility_rule.employment_statuses' => ['sometimes', 'array'],
            'eligibility_rule.employment_statuses.*' => [
                'string',
                Rule::in(['active', 'inactive', 'probation', 'notice_period', 'terminated']),
                'distinct',
            ],
            'eligibility_rule.genders' => ['sometimes', 'array'],
            'eligibility_rule.genders.*' => [
                'string',
                Rule::in(['male', 'female', 'non_binary', 'prefer_not_to_say']),
                'distinct',
            ],
            'eligibility_rule.marital_statuses' => ['sometimes', 'array'],
            'eligibility_rule.marital_statuses.*' => [
                'string',
                Rule::in(['single', 'married', 'divorced', 'widowed']),
                'distinct',
            ],
            'eligibility_rule.minimum_tenure_days' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:36500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
