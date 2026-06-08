<?php

namespace App\Modules\AttendanceManagement\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RecalculateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'employee_id' => ['sometimes', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('date_to')) {
                return;
            }

            $today = now($this->user()?->company?->timezone ?? config('app.timezone'))->startOfDay();
            $dateTo = Carbon::parse($this->string('date_to')->toString())->startOfDay();

            if ($dateTo->gt($today)) {
                $validator->errors()->add('date_to', 'Attendance recalculation is only supported up to the current working date.');
            }
        });
    }
}
