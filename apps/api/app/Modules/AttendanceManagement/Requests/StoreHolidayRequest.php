<?php

namespace App\Modules\AttendanceManagement\Requests;

use App\Models\Holiday;
use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHolidayRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:150'],
            'holiday_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['national', 'regional', 'company', 'optional'])],
            'is_optional' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('holiday_date') || ! $this->filled('name')) {
                return;
            }

            $holidayCalendarId = (int) $this->route('holidayCalendarId');

            $duplicateExists = Holiday::withoutGlobalScopes()
                ->where('company_id', $this->user()?->company_id)
                ->where('holiday_calendar_id', $holidayCalendarId)
                ->where('holiday_date', '>=', $this->string('holiday_date')->toString())
                ->where('holiday_date', '<', Carbon::parse($this->string('holiday_date')->toString())->addDay()->toDateString())
                ->where('name', $this->string('name')->toString())
                ->exists();

            if ($duplicateExists) {
                $validator->errors()->add(
                    'holiday_date',
                    'A holiday with the same name and date already exists in this calendar.',
                );
            }
        });
    }
}
