<?php

namespace App\Modules\IntegrationsPlatform\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationConnectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'system_key' => $this->system_key,
            'version' => $this->version,
            'name' => $this->name,
            'direction' => $this->direction,
            'status' => $this->status,
            'auth_mode' => $this->auth_mode,
            'endpoint_url' => $this->endpoint_url,
            'description' => $this->description,
            'scopes' => $this->scopes ?? [],
            'settings' => $this->settings ?? [],
            'active_subscription_count' => $this->whenCounted('webhookSubscriptions'),
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
