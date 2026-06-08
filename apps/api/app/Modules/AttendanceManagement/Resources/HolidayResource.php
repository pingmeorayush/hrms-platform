<?php

namespace App\Modules\AttendanceManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'holiday_calendar_id' => $this->holiday_calendar_id,
            'name' => $this->name,
            'holiday_date' => $this->holiday_date?->toDateString(),
            'type' => $this->type,
            'is_optional' => $this->is_optional,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
