<?php

namespace App\Modules\AttendanceManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAttendanceCorrectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer'],
            'attendance_record_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'changes_requested'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
