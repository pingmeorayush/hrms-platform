<?php

namespace App\Modules\AttendanceManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendancePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'working_hours_minutes' => ['required', 'integer', 'min:60', 'max:1440'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'late_after_minutes' => ['required', 'integer', 'min:0', 'max:180', 'gte:grace_minutes'],
            'half_day_minutes' => ['required', 'integer', 'min:1', 'lt:working_hours_minutes'],
            'overtime_eligible' => ['required', 'boolean'],
            'overtime_after_minutes' => ['nullable', 'integer', 'min:1', 'gte:working_hours_minutes', 'required_if:overtime_eligible,true'],
            'weekend_rule' => ['required', 'array'],
            'weekend_rule.non_working_days' => ['required', 'array', 'min:1'],
            'weekend_rule.non_working_days.*' => ['integer', Rule::in([0, 1, 2, 3, 4, 5, 6]), 'distinct'],
            'work_from_home_allowed' => ['required', 'boolean'],
            'enforce_geofence' => ['required', 'boolean'],
            'allowed_radius_meters' => ['nullable', 'integer', 'min:1', 'max:100000', 'required_if:enforce_geofence,true'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
