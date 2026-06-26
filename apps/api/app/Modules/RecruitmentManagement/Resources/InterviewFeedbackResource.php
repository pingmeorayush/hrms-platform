<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'recommendation' => $this->recommendation,
            'comments' => $this->comments,
            'strengths' => $this->strengths,
            'concerns' => $this->concerns,
            'interviewer' => new UserReferenceResource($this->whenLoaded('interviewer')),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
        ];
    }
}
