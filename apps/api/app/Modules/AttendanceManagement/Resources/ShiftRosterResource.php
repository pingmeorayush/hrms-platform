<?php

namespace App\Modules\AttendanceManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftRosterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => new EmployeeReferenceResource($this->whenLoaded('employee')),
            'shift' => new ShiftResource($this->whenLoaded('shift')),
            'work_date' => $this->work_date?->toDateString(),
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
