<?php

namespace App\Modules\AttendanceManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreShiftRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            $entries = collect($this->input('entries', []));
            $keys = $entries
                ->map(fn (array $entry): string => ($entry['employee_id'] ?? '').'|'.($entry['work_date'] ?? ''))
                ->filter()
                ->countBy();

            foreach ($entries as $index => $entry) {
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
