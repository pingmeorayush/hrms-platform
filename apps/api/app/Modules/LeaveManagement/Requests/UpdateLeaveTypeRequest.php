<?php

namespace App\Modules\LeaveManagement\Requests;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;
        $leaveTypeId = (int) $this->route('leaveTypeId');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique(LeaveType::class, 'code')->ignore($leaveTypeId)->where('company_id', $companyId),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique(LeaveType::class, 'name')->ignore($leaveTypeId)->where('company_id', $companyId),
            ],
            'category' => ['required', Rule::in(['earned', 'casual', 'sick', 'optional', 'unpaid'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_paid' => ['required', 'boolean'],
            'requires_approval' => ['required', 'boolean'],
            'allows_half_day' => ['required', 'boolean'],
            'color_token' => ['required', 'string', 'regex:/^#(?:[0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
