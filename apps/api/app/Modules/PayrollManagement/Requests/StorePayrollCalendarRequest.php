<?php

namespace App\Modules\PayrollManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePayrollCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'frequency' => ['required', Rule::in(['monthly', 'weekly', 'biweekly', 'semi_monthly', 'custom'])],
            'timezone' => ['required', 'timezone'],
            'payroll_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'payroll_weekday' => ['nullable', 'integer', 'min:0', 'max:6'],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $frequency = (string) $this->input('frequency');
                $payrollDay = $this->input('payroll_day');
                $payrollWeekday = $this->input('payroll_weekday');

                if (in_array($frequency, ['monthly', 'semi_monthly', 'custom'], true) && $payrollDay === null) {
                    $validator->errors()->add('payroll_day', 'A payroll day is required for the selected payroll frequency.');
                }

                if (in_array($frequency, ['weekly', 'biweekly'], true) && $payrollWeekday === null) {
                    $validator->errors()->add('payroll_weekday', 'A payroll weekday is required for the selected payroll frequency.');
                }
            },
        ];
    }
}
