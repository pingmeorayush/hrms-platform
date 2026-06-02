<?php

namespace App\Modules\Platform\Workflow\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowInstanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'status' => $this->status,
            'current_stage_sequence' => $this->current_stage_sequence,
            'payload' => $this->payload ?? [],
            'completed_at' => $this->completed_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'definition' => $this->whenLoaded('definition', fn () => [
                'id' => $this->definition?->id,
                'key' => $this->definition?->key,
                'name' => $this->definition?->name,
            ]),
            'starter' => $this->whenLoaded('starter', fn () => [
                'id' => $this->starter?->id,
                'name' => $this->starter?->name,
                'email' => $this->starter?->email,
            ]),
            'tasks' => WorkflowTaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
