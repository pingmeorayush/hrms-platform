<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'interview_code' => $this->interview_code,
            'round_number' => $this->round_number,
            'interview_type' => $this->interview_type,
            'status' => $this->status,
            'timezone' => $this->timezone,
            'scheduled_start_at' => $this->scheduled_start_at?->toIso8601String(),
            'scheduled_end_at' => $this->scheduled_end_at?->toIso8601String(),
            'meeting_mode' => $this->meeting_mode,
            'meeting_location' => $this->meeting_location,
            'meeting_link' => $this->meeting_link,
            'agenda' => $this->agenda,
            'cancellation_reason' => $this->cancellation_reason,
            'candidate' => new CandidateResource($this->whenLoaded('candidate')),
            'requisition' => new JobRequisitionReferenceResource($this->whenLoaded('requisition')),
            'interviewer' => new UserReferenceResource($this->whenLoaded('interviewer')),
            'feedback' => $this->whenLoaded(
                'feedback',
                fn () => $this->feedback ? new InterviewFeedbackResource($this->feedback) : null,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
