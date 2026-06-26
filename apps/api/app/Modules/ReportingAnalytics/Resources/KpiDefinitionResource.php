<?php

namespace App\Modules\ReportingAnalytics\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'domain' => $this->domain,
            'description' => $this->description,
            'formula' => $this->formula,
            'source_references' => $this->source_references ?? [],
            'grain' => $this->grain,
            'owner' => $this->whenLoaded(
                'owner',
                fn () => new UserReferenceResource($this->owner),
            ),
            'governance' => [
                'version' => $this->version,
                'certification_status' => $this->certification_status,
                'review_notes' => $this->review_notes,
                'reviewed_by' => $this->whenLoaded(
                    'reviewedBy',
                    fn () => new UserReferenceResource($this->reviewedBy),
                ),
                'reviewed_at' => $this->reviewed_at?->toIso8601String(),
                'certified_by' => $this->whenLoaded(
                    'certifiedBy',
                    fn () => new UserReferenceResource($this->certifiedBy),
                ),
                'certified_at' => $this->certified_at?->toIso8601String(),
            ],
            'created_by' => $this->whenLoaded(
                'createdBy',
                fn () => new UserReferenceResource($this->createdBy),
            ),
            'updated_by' => $this->whenLoaded(
                'updatedBy',
                fn () => new UserReferenceResource($this->updatedBy),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
