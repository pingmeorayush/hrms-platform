<?php

namespace App\Modules\AttendanceManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'employee' => new EmployeeReferenceResource($this->whenLoaded('employee')),
            'shift' => $this->shift ? new ShiftResource($this->shift) : null,
            'shift_roster_id' => $this->shift_roster_id,
            'state' => $this->check_in_at === null ? 'not_captured' : ($this->check_out_at === null ? 'checked_in' : 'checked_out'),
            'worked_minutes' => $this->worked_minutes,
            'calculation' => [
                'primary_status' => $this->primary_status,
                'scheduled_start_at' => $this->scheduled_start_at?->toIso8601String(),
                'scheduled_end_at' => $this->scheduled_end_at?->toIso8601String(),
                'scheduled_work_minutes' => $this->scheduled_work_minutes,
                'break_duration_minutes' => $this->break_duration_minutes,
                'is_late' => (bool) $this->is_late,
                'late_minutes' => $this->late_minutes,
                'is_half_day' => (bool) $this->is_half_day,
                'overtime_minutes' => $this->overtime_minutes,
                'is_weekend' => (bool) $this->is_weekend,
                'is_holiday' => (bool) $this->is_holiday,
                'holiday_name' => $this->holiday_name,
                'is_early_departure' => (bool) $this->is_early_departure,
                'early_departure_minutes' => $this->early_departure_minutes,
                'calculated_at' => $this->calculated_at?->toIso8601String(),
                'metadata' => (object) ($this->calculation_metadata ?? []),
            ],
            'check_in' => [
                'at' => $this->check_in_at?->toIso8601String(),
                'channel' => $this->check_in_channel,
                'ip_address' => $this->check_in_ip_address,
                'user_agent' => $this->check_in_user_agent,
                'metadata' => (object) ($this->check_in_metadata ?? []),
            ],
            'check_out' => [
                'at' => $this->check_out_at?->toIso8601String(),
                'channel' => $this->check_out_channel,
                'ip_address' => $this->check_out_ip_address,
                'user_agent' => $this->check_out_user_agent,
                'metadata' => (object) ($this->check_out_metadata ?? []),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
