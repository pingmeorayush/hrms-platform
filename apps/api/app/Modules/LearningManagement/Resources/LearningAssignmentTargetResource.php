<?php

namespace App\Modules\LearningManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\LearningManagement\Services\LearningTrackingStateResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningAssignmentTargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var LearningTrackingStateResolver $trackingStateResolver */
        $trackingStateResolver = app(LearningTrackingStateResolver::class);
        $state = $trackingStateResolver->summarizeTarget($this->resource);

        return [
            'id' => $this->id,
            'assignment' => [
                'id' => $this->assignment?->id,
                'assignment_code' => $this->assignment?->assignment_code,
                'status' => $this->assignment?->status,
                'audience_type' => $this->assignment?->audience_type,
            ],
            'item' => new LearningItemResource($this->whenLoaded('item')),
            'employee' => new EmployeeReferenceResource($this->whenLoaded('employee')),
            'status' => $this->status,
            'completion_progress_percent' => $this->completion_progress_percent,
            'due_on' => $this->due_on?->toDateString(),
            'due_state' => $state['due_state'],
            'renewal_due_on' => $this->renewal_due_on?->toDateString(),
            'renewal_posture' => $state['renewal_posture'],
            'requires_completion_evidence' => $state['requires_completion_evidence'],
            'evidence_present' => $state['evidence_present'],
            'completion_notes' => $this->completion_notes,
            'completion_evidence' => $this->completion_evidence,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'completed_by' => $this->whenLoaded('completedBy', fn (): ?array => $this->completedBy ? [
                'id' => $this->completedBy->id,
                'name' => $this->completedBy->name,
            ] : null),
            'assigned_on' => $this->assigned_on?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
