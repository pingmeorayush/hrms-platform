<?php

namespace App\Modules\AttendanceManagement\Requests;

use App\Modules\Platform\Shared\Requests\Concerns\AuthorizesRoutePermissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceCheckOutRequest extends FormRequest
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
            'channel' => ['sometimes', Rule::in(['web', 'api'])],
            'captured_at' => ['sometimes', 'date'],
            'device' => ['sometimes', 'array'],
            'device.device_id' => ['sometimes', 'nullable', 'string', 'max:120'],
            'device.device_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'device.platform' => ['sometimes', 'nullable', 'string', 'max:80'],
            'device.browser' => ['sometimes', 'nullable', 'string', 'max:80'],
            'device.app_version' => ['sometimes', 'nullable', 'string', 'max:40'],
            'geolocation' => ['sometimes', 'array'],
            'geolocation.latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'geolocation.longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'geolocation.accuracy_meters' => ['sometimes', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
