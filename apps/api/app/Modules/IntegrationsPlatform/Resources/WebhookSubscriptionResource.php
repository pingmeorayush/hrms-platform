<?php

namespace App\Modules\IntegrationsPlatform\Resources;

use App\Modules\IntegrationsPlatform\Resources\Concerns\FormatsIntegrationResourceData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookSubscriptionResource extends JsonResource
{
    use FormatsIntegrationResourceData;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_key' => $this->subscription_key,
            'integration_connection_id' => $this->integration_connection_id,
            'version' => $this->version,
            'event_key' => $this->event_key,
            'direction' => $this->direction,
            'status' => $this->status,
            'endpoint_url' => $this->endpoint_url,
            'secret_preview' => $this->maskSecret($this->secret),
            'custom_headers' => $this->redactHeaders($this->custom_headers),
            'filter_rules' => $this->filter_rules ?? [],
            'connection' => $this->whenLoaded('connection', fn (): array => [
                'id' => $this->connection?->id,
                'system_key' => $this->connection?->system_key,
                'name' => $this->connection?->name,
                'status' => $this->connection?->status,
            ]),
            'last_delivery_at' => $this->last_delivery_at?->toIso8601String(),
            'last_received_at' => $this->last_received_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
