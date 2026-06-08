<?php

namespace App\Modules\PayrollManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollRunActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'string', 'max:500'],
            'comment' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
