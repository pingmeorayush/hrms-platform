<?php

namespace App\Modules\AttendanceManagement\Resources;

use App\Modules\OrganizationManagement\Resources\DepartmentResource;
use App\Modules\OrganizationManagement\Resources\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayCalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'location' => new LocationResource($this->whenLoaded('location')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'is_default' => $this->is_default,
            'status' => $this->status,
            'holidays' => HolidayResource::collection($this->whenLoaded('holidays')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
