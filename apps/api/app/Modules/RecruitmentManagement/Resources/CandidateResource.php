<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'candidate_code' => $this->candidate_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim(implode(' ', array_filter([$this->first_name, $this->last_name]))),
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'current_stage' => $this->current_stage,
            'status' => $this->status,
            'stage_entered_at' => $this->stage_entered_at?->toIso8601String(),
            'total_experience_years' => $this->total_experience_years,
            'notice_period_days' => $this->notice_period_days,
            'current_company' => $this->current_company,
            'current_title' => $this->current_title,
            'summary' => $this->summary,
            'notes' => $this->notes,
            'recruiter' => new UserReferenceResource($this->whenLoaded('recruiter')),
            'requisition' => new JobRequisitionReferenceResource($this->whenLoaded('requisition')),
            'resume_count' => $this->whenCounted('resumes'),
            'latest_resume' => $this->whenLoaded(
                'resumes',
                fn () => $this->resumes->isNotEmpty() ? new CandidateResumeResource($this->resumes->first()) : null,
            ),
            'resumes' => CandidateResumeResource::collection($this->whenLoaded('resumes')),
            'stage_history' => CandidateStageTransitionResource::collection($this->whenLoaded('stageTransitions')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
