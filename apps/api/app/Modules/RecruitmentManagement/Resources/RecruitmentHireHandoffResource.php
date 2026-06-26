<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\EmployeeManagement\Resources\EmployeeReferenceResource;
use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecruitmentHireHandoffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'offer' => $this->whenLoaded('offer', fn () => $this->offer ? [
                'id' => $this->offer->id,
                'offer_code' => $this->offer->offer_code,
                'status' => $this->offer->status,
                'employment_type' => $this->offer->employment_type,
                'proposed_start_date' => $this->offer->proposed_start_date?->toDateString(),
                'expires_on' => $this->offer->expires_on?->toDateString(),
            ] : null),
            'candidate' => new CandidateResource($this->whenLoaded('candidate')),
            'requisition' => new JobRequisitionReferenceResource($this->whenLoaded('requisition')),
            'employee' => new EmployeeReferenceResource($this->whenLoaded('employee')),
            'recruiter' => new UserReferenceResource($this->whenLoaded('recruiter')),
            'converted_by' => new UserReferenceResource($this->whenLoaded('convertedBy')),
            'source_resume' => $this->whenLoaded('sourceResume', fn () => $this->sourceResume ? [
                'id' => $this->sourceResume->id,
                'version_number' => $this->sourceResume->version_number,
                'original_file_name' => $this->sourceResume->original_file_name,
            ] : null),
            'offer_snapshot' => $this->offer_snapshot ?? [],
            'candidate_snapshot' => $this->candidate_snapshot ?? [],
            'requisition_snapshot' => $this->requisition_snapshot ?? [],
            'document_references' => $this->document_references ?? [],
            'onboarding_template_ids' => $this->onboarding_template_ids ?? [],
            'onboarding_task_ids' => $this->onboarding_task_ids ?? [],
            'notes' => $this->notes,
            'converted_at' => $this->converted_at?->toIso8601String(),
            'onboarding_triggered_at' => $this->onboarding_triggered_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
