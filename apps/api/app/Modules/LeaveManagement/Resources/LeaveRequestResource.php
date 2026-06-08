<?php

namespace App\Modules\LeaveManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\OrganizationManagement\Resources\DepartmentResource;
use App\Modules\OrganizationManagement\Resources\LocationResource;
use App\Modules\Platform\Workflow\Resources\WorkflowInstanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => new EmployeeReferenceResource($this->whenLoaded('employee')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'leave_policy_id' => $this->leave_policy_id,
            'policy_version' => $this->policy_version,
            'workflow_instance_id' => $this->workflow_instance_id,
            'workflow' => $this->whenLoaded(
                'workflowInstance',
                fn () => new WorkflowInstanceResource($this->workflowInstance),
            ),
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_days' => $this->total_days,
            'status' => $this->status,
            'reason' => $this->reason,
            'approver_comment' => $this->approver_comment,
            'can_cancel' => in_array($this->status, ['pending', 'approved'], true),
            'is_auto_approved' => (bool) $this->is_auto_approved,
            'attendance_sync_status' => $this->attendance_sync_status,
            'attendance_synced_at' => $this->attendance_synced_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
