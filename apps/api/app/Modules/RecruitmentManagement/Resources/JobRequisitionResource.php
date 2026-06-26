<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\OrganizationManagement\Resources\CostCenterResource;
use App\Modules\OrganizationManagement\Resources\DepartmentResource;
use App\Modules\OrganizationManagement\Resources\DesignationResource;
use App\Modules\OrganizationManagement\Resources\LocationResource;
use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use App\Modules\Platform\Workflow\Resources\WorkflowInstanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobRequisitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requisition_code' => $this->requisition_code,
            'title' => $this->title,
            'employment_type' => $this->employment_type,
            'hiring_type' => $this->hiring_type,
            'priority' => $this->priority,
            'openings_count' => $this->openings_count,
            'min_experience_years' => $this->min_experience_years,
            'target_start_date' => $this->target_start_date?->toDateString(),
            'headcount_reference' => $this->headcount_reference,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'designation' => new DesignationResource($this->whenLoaded('designation')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'recruiter' => new UserReferenceResource($this->whenLoaded('recruiter')),
            'hiring_manager' => new EmployeeReferenceResource($this->whenLoaded('hiringManager')),
            'requested_by' => new UserReferenceResource($this->whenLoaded('requestedBy')),
            'workflow_instance_id' => $this->workflow_instance_id,
            'workflow' => $this->whenLoaded(
                'workflowInstance',
                fn () => new WorkflowInstanceResource($this->workflowInstance),
            ),
            'status' => $this->status,
            'status_before_hold' => $this->status_before_hold,
            'justification' => $this->justification,
            'notes' => $this->notes,
            'closed_reason' => $this->closed_reason,
            'can_edit_details' => in_array($this->status, ['draft', 'rejected', 'changes_requested', 'on_hold'], true),
            'can_submit' => in_array($this->status, ['draft', 'rejected', 'changes_requested'], true),
            'can_put_on_hold' => in_array($this->status, ['draft', 'approved', 'rejected', 'changes_requested'], true),
            'can_resume' => $this->status === 'on_hold',
            'can_close' => $this->status !== 'closed',
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'on_hold_at' => $this->on_hold_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
