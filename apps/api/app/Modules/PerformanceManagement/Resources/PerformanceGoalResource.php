<?php

namespace App\Modules\PerformanceManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\OrganizationManagement\Resources\DepartmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceGoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'goal_code' => $this->goal_code,
            'goal_type' => $this->goal_type,
            'title' => $this->title,
            'description' => $this->description,
            'review_cycle' => $this->whenLoaded('reviewCycle', fn (): array => [
                'id' => $this->reviewCycle->id,
                'code' => $this->reviewCycle->code,
                'name' => $this->reviewCycle->name,
                'status' => $this->reviewCycle->status,
            ]),
            'owner_employee' => new EmployeeReferenceResource($this->whenLoaded('ownerEmployee')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'due_on' => $this->due_on?->toDateString(),
            'weight_percent' => $this->weight_percent,
            'success_metric' => $this->success_metric,
            'status' => $this->status,
            'can_edit_configuration' => $this->status !== 'archived',
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
