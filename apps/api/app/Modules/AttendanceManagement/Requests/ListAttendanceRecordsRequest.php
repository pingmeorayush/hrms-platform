<?php

namespace App\Modules\AttendanceManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAttendanceRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'employee_id' => ['sometimes', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'primary_status' => ['sometimes', Rule::in(['present', 'half_day', 'absent', 'holiday', 'weekend', 'incomplete', 'leave'])],
            'state' => ['sometimes', Rule::in(['checked_in', 'checked_out'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
