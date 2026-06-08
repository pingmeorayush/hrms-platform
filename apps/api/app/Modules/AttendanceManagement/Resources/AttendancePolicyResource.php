<?php

namespace App\Modules\AttendanceManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendancePolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'working_hours_minutes' => $this->working_hours_minutes,
            'grace_minutes' => $this->grace_minutes,
            'late_after_minutes' => $this->late_after_minutes,
            'half_day_minutes' => $this->half_day_minutes,
            'overtime_eligible' => $this->overtime_eligible,
            'overtime_after_minutes' => $this->overtime_after_minutes,
            'weekend_rule' => $this->weekend_rule,
            'work_from_home_allowed' => $this->work_from_home_allowed,
            'enforce_geofence' => $this->enforce_geofence,
            'allowed_radius_meters' => $this->allowed_radius_meters,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
