<?php

namespace App\Modules\Platform\Workflow\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowStageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'sequence' => $this->sequence,
            'approver_type' => $this->approver_type,
            'approver_value' => $this->approver_value,
            'available_actions' => $this->available_actions ?? [],
            'sla_hours' => $this->sla_hours,
            'metadata' => $this->metadata ?? [],
        ];
    }
}
