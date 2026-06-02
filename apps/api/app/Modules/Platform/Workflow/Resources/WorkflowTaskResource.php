<?php

namespace App\Modules\Platform\Workflow\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stage_key' => $this->stage_key,
            'stage_name' => $this->stage_name,
            'sequence' => $this->sequence,
            'status' => $this->status,
            'available_actions' => $this->available_actions ?? [],
            'decision' => $this->decision,
            'decision_comment' => $this->decision_comment,
            'due_at' => $this->due_at?->toIso8601String(),
            'acted_at' => $this->acted_at?->toIso8601String(),
            'assigned_to_role' => $this->assigned_to_role,
            'assignee' => $this->whenLoaded('assignee', fn () => [
                'id' => $this->assignee?->id,
                'name' => $this->assignee?->name,
                'email' => $this->assignee?->email,
            ]),
            'actor' => $this->whenLoaded('actor', fn () => [
                'id' => $this->actor?->id,
                'name' => $this->actor?->name,
                'email' => $this->actor?->email,
            ]),
        ];
    }
}
