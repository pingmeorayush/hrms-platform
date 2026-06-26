<?php

namespace App\Modules\AttendanceManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAttendanceCorrectionRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|Rule|array<int, \Closure|Rule|ValidationRule|string>|string>
     */
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
