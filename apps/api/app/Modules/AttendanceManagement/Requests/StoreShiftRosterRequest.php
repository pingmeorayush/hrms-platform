<?php

namespace App\Modules\AttendanceManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreShiftRosterRequest extends FormRequest
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
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'entries.*.shift_id' => ['required', 'integer', Rule::exists('shifts', 'id')->where('company_id', $companyId)],
            'entries.*.work_date' => ['required', 'date'],
            'entries.*.notes' => ['nullable', 'string'],
            'entries.*.status' => ['nullable', Rule::in(['scheduled', 'cancelled'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $entries = $this->input('entries', []);

            if (! is_array($entries)) {
                return;
            }

            $keys = [];

            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $key = ($entry['employee_id'] ?? '').'|'.($entry['work_date'] ?? '');

                if ($key === '|') {
                    continue;
                }

                $keys[$key] = ($keys[$key] ?? 0) + 1;
            }

            foreach ($entries as $index => $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $key = ($entry['employee_id'] ?? '').'|'.($entry['work_date'] ?? '');

                if (($keys[$key] ?? 0) > 1) {
                    $validator->errors()->add(
                        "entries.{$index}.work_date",
                        'Roster entries must be unique per employee and work date within the request.',
                    );
                }
            }
        });
    }
}
