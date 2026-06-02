<?php

namespace App\Modules\Platform\Workflow\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'module' => $this->module,
            'description' => $this->description,
            'is_template' => $this->is_template,
            'status' => $this->status,
            'active_version_id' => $this->active_version_id,
            'active_version' => new WorkflowVersionResource($this->whenLoaded('activeVersion')),
            'versions' => WorkflowVersionResource::collection($this->whenLoaded('versions')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
