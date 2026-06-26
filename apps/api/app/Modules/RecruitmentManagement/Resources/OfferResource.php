<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use App\Modules\Platform\Workflow\Resources\WorkflowInstanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'offer_code' => $this->offer_code,
            'status' => $this->status,
            'employment_type' => $this->employment_type,
            'currency' => $this->currency,
            'annual_ctc_amount' => $this->annual_ctc_amount,
            'joining_bonus_amount' => $this->joining_bonus_amount,
            'proposed_start_date' => $this->proposed_start_date?->toDateString(),
            'expires_on' => $this->expires_on?->toDateString(),
            'notes' => $this->notes,
            'candidate_message' => $this->candidate_message,
            'candidate' => new CandidateResource($this->whenLoaded('candidate')),
            'requisition' => new JobRequisitionReferenceResource($this->whenLoaded('requisition')),
            'recruiter' => new UserReferenceResource($this->whenLoaded('recruiter')),
            'requested_by' => new UserReferenceResource($this->whenLoaded('requestedBy')),
            'workflow_instance_id' => $this->workflow_instance_id,
            'workflow' => $this->whenLoaded(
                'workflowInstance',
                fn () => new WorkflowInstanceResource($this->workflowInstance),
            ),
            'hire_handoff' => $this->whenLoaded('handoff', fn () => $this->handoff ? [
                'id' => $this->handoff->id,
                'status' => $this->handoff->status,
                'employee' => new EmployeeReferenceResource($this->handoff->employee),
                'converted_at' => $this->handoff->converted_at?->toIso8601String(),
                'onboarding_triggered_at' => $this->handoff->onboarding_triggered_at?->toIso8601String(),
            ] : null),
            'decision_history' => OfferDecisionResource::collection($this->whenLoaded('decisions')),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'declined_at' => $this->declined_at?->toIso8601String(),
            'expired_at' => $this->expired_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
