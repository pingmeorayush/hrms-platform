<?php

namespace App\Modules\AttendanceManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'shift_id' => ['required', 'integer', Rule::exists('shifts', 'id')->where('company_id', $companyId)],
            'work_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['scheduled', 'cancelled'])],
        ];
    }
}
