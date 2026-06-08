<?php

namespace App\Modules\LeaveManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['cancel', 'approve', 'reject', 'request_changes'])],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
