<?php

namespace App\Modules\AttendanceManagement\Requests;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreShiftRequest extends FormRequest
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
                Rule::unique(Shift::class, 'code')->where('company_id', $companyId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'break_duration_minutes' => ['required', 'integer', 'min:0', 'max:720'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'working_hours_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('start_time') || ! $this->filled('end_time')) {
                return;
            }

            if ($this->string('start_time')->toString() === $this->string('end_time')->toString()) {
                $validator->errors()->add('end_time', 'End time must differ from start time.');
            }
        });
    }
}
