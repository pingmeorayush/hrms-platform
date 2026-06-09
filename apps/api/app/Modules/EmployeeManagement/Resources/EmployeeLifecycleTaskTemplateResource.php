<?php

namespace App\Modules\EmployeeManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLifecycleTaskTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lifecycle_type' => $this->lifecycle_type,
            'title' => $this->title,
            'category' => $this->category,
            'task_type' => $this->task_type,
            'assignee_type' => $this->assignee_type,
            'requires_approval' => (bool) $this->requires_approval,
            'approval_workflow_key' => $this->approval_workflow_key,
            'due_offset_days' => $this->due_offset_days,
            'sort_order' => $this->sort_order,
            'notes' => $this->notes,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
