<?php

namespace App\Modules\LearningManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'delivery_mode' => $this->delivery_mode,
            'duration_minutes' => $this->duration_minutes,
            'requires_completion_evidence' => (bool) $this->requires_completion_evidence,
            'renewal_frequency_months' => $this->renewal_frequency_months,
            'default_due_days' => $this->default_due_days,
            'metadata' => $this->metadata,
            'status' => $this->status,
            'created_by' => $this->whenLoaded('createdBy', fn (): array => [
                'id' => $this->createdBy?->id,
                'name' => $this->createdBy?->name,
            ]),
            'updated_by' => $this->whenLoaded('updatedBy', fn (): array => [
                'id' => $this->updatedBy?->id,
                'name' => $this->updatedBy?->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
