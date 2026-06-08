<?php

namespace App\Modules\AttendanceManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\OrganizationManagement\Resources\DepartmentResource;
use App\Modules\OrganizationManagement\Resources\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_type' => $this->assignment_type,
            'shift' => new ShiftResource($this->whenLoaded('shift')),
            'employee' => $this->employee ? new EmployeeReferenceResource($this->employee) : null,
            'department' => $this->department ? new DepartmentResource($this->department) : null,
            'location' => $this->location ? new LocationResource($this->location) : null,
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_to' => $this->effective_to?->toDateString(),
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
