<?php

namespace App\Modules\EmployeeManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class EmployeeOnboardingTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'lifecycle_type' => $this->lifecycle_type,
            'template_id' => $this->template_id,
            'title' => $this->title,
            'category' => $this->category,
            'task_type' => $this->task_type,
            'assignee_type' => $this->assignee_type,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'assigned_to_user_name' => $this->assignedTo?->name,
            'requires_approval' => (bool) $this->requires_approval,
            'approval_workflow_key' => $this->approval_workflow_key,
            'workflow_instance_id' => $this->workflow_instance_id,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'due_date' => $this->due_date?->toDateString(),
            'due_state' => $this->resolveDueState(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'completed_by_user_id' => $this->completed_by_user_id,
            'latest_action_by_user_id' => $this->latest_action_by_user_id,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function resolveDueState(): string
    {
        if ($this->due_date === null) {
            return 'no_due_date';
        }

        if (in_array($this->status, ['completed', 'skipped'], true)) {
            return 'closed';
        }

        $today = Carbon::today($this->employee?->company?->timezone ?? config('app.timezone'));

        if ($this->due_date->lt($today)) {
            return 'overdue';
        }

        if ($this->due_date->equalTo($today)) {
            return 'due_today';
        }

        return 'upcoming';
    }
}
