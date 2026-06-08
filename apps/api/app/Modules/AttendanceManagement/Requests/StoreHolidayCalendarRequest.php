<?php

namespace App\Modules\AttendanceManagement\Requests;

use App\Models\HolidayCalendar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHolidayCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(HolidayCalendar::class, 'code')->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->where('company_id', $companyId)],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'is_default' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->boolean('is_default')) {
                return;
            }

            if ($this->filled('location_id') || $this->filled('department_id')) {
                $validator->errors()->add(
                    'is_default',
                    'Default holiday calendars cannot be scoped to a specific location or department.',
                );
            }
        });
    }
}
