<?php

namespace App\Modules\AttendanceManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_record_id' => ['required', 'integer'],
            'reason' => ['required', 'string'],
            'corrected' => ['required', 'array'],
            'corrected.check_in_at' => ['nullable', 'date'],
            'corrected.check_out_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $corrected = $this->input('corrected', []);

            if (! is_array($corrected)) {
                return;
            }

            $hasCheckIn = array_key_exists('check_in_at', $corrected) && filled($corrected['check_in_at']);
            $hasCheckOut = array_key_exists('check_out_at', $corrected) && filled($corrected['check_out_at']);

            if (! $hasCheckIn && ! $hasCheckOut) {
                $validator->errors()->add('corrected', 'At least one corrected timestamp must be supplied.');
            }
        });
    }
}
