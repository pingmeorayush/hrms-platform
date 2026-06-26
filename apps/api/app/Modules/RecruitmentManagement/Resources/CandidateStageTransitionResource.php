<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateStageTransitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_stage' => $this->from_stage,
            'to_stage' => $this->to_stage,
            'resulting_status' => $this->resulting_status,
            'comment' => $this->comment,
            'transitioned_by' => new UserReferenceResource($this->whenLoaded('actor')),
            'transitioned_at' => $this->transitioned_at?->toIso8601String(),
        ];
    }
}
