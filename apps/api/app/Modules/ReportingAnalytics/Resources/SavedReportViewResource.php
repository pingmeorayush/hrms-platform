<?php

namespace App\Modules\ReportingAnalytics\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedReportViewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'view_uuid' => $this->view_uuid,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'share' => [
                'scope' => $this->share_scope,
                'shared_role_names' => $this->shared_role_names ?? [],
            ],
            'dataset' => $this->whenLoaded('reportDataset', fn (): array => [
                'id' => $this->reportDataset?->id,
                'key' => $this->reportDataset?->key,
                'name' => $this->reportDataset?->name,
                'domain' => $this->reportDataset?->domain,
            ]),
            'owner' => $this->whenLoaded(
                'owner',
                fn () => new UserReferenceResource($this->owner),
            ),
            'query' => [
                'filters' => $this->filters ?? [],
                'filter_operators' => $this->filter_operators ?? [],
                'sort_by' => $this->sort_by,
                'sort_direction' => $this->sort_direction,
                'drilldown_path' => $this->drilldown_path,
            ],
            'presentation_preferences' => $this->presentation_preferences ?? [],
            'validation' => $this->validation_state ?? [
                'status' => 'unknown',
                'reason' => null,
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
