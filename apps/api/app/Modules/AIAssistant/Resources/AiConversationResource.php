<?php

namespace App\Modules\AIAssistant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'persona' => $this->persona,
            'status' => $this->status,
            'metadata' => $this->metadata ?? (object) [],
            'last_interacted_at' => $this->last_interacted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
