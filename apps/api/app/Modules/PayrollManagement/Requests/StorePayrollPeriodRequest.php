<?php

namespace App\Modules\PayrollManagement\Requests;

use App\Models\PayrollCalendar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayrollPeriodRequest extends FormRequest
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
                'required',
                'integer',
                Rule::exists(PayrollCalendar::class, 'id')->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'payroll_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
