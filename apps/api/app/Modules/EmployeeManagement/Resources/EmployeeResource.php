<?php

namespace App\Modules\EmployeeManagement\Resources;

use App\Modules\OrganizationManagement\Resources\CostCenterResource;
use App\Modules\OrganizationManagement\Resources\DepartmentResource;
use App\Modules\OrganizationManagement\Resources\DesignationResource;
use App\Modules\OrganizationManagement\Resources\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'date_of_joining' => $this->date_of_joining?->toDateString(),
            'employment_type' => $this->employment_type,
            'employment_status' => $this->employment_status,
            'termination_reason' => $this->termination_reason,
            'terminated_at' => $this->terminated_at?->toIso8601String(),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'designation' => new DesignationResource($this->whenLoaded('designation')),
            'manager' => new EmployeeReferenceResource($this->whenLoaded('manager')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
