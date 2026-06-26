<?php

namespace App\Modules\RecruitmentManagement\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferDecisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'decision_type' => $this->decision_type,
            'comment' => $this->comment,
            'actor' => new UserReferenceResource($this->whenLoaded('actor')),
            'acted_at' => $this->acted_at?->toIso8601String(),
        ];
    }
}
