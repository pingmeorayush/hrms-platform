<?php

namespace App\Modules\AssetManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = $this->whenLoaded('assetCategory');

        return [
            'id' => $this->id,
            'asset_category_id' => $this->asset_category_id,
            'asset_category' => $category ? [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
                'status' => $category->status,
            ] : null,
            'asset_tag' => $this->asset_tag,
            'name' => $this->name,
            'asset_type' => $this->asset_type,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'model_name' => $this->model_name,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'current_assignment' => new AssetAssignmentResource($this->whenLoaded('currentAssignment')),
            'assignment_history' => AssetAssignmentResource::collection($this->whenLoaded('assignments')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
