<?php

namespace App\Modules\DocumentManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'repository_scope' => $this->repository_scope,
            'default_visibility_scope' => $this->default_visibility_scope,
            'retention_days' => $this->retention_days,
            'allowed_role_names' => $this->allowed_role_names ?? [],
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
