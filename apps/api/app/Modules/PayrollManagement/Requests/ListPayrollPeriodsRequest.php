<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\PayrollCalendar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPayrollPeriodsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'payroll_calendar_id' => [
                'sometimes',
                'integer',
                Rule::exists(PayrollCalendar::class, 'id')->where('company_id', $companyId),
            ],
            'status' => ['sometimes', Rule::in(['draft', 'open', 'prepared', 'closed'])],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
