<?php

namespace App\Modules\AttendanceManagement\Requests;

use App\Models\User;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RecalculateAttendanceRequest extends FormRequest
{
    use AuthorizesRoutePermissions;

    public function authorize(): bool
    {
        return $this->authorizeFromRoutePermissions();
    }

    /**
     * @return array<string, ValidationRule|\Illuminate\Contracts\Validation\Rule|array<int, \Closure|\Illuminate\Contracts\Validation\Rule|ValidationRule|string>|string>
     */
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

            $timezone = config('app.timezone');
            $user = $this->user();

            if ($user instanceof User) {
                $timezone = $user->company()->value('timezone') ?? $timezone;
            }

            $today = now($timezone)->startOfDay();
            $dateTo = Carbon::parse($this->string('date_to')->toString())->startOfDay();

            if ($dateTo->gt($today)) {
                $validator->errors()->add('date_to', 'Attendance recalculation is only supported up to the current working date.');
            }
        });
    }
}
