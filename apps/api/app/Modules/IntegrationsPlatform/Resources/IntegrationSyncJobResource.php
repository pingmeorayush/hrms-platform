<?php

namespace App\Modules\IntegrationsPlatform\Resources;

use App\Models\IntegrationSyncError;
use App\Modules\IntegrationsPlatform\Resources\Concerns\FormatsIntegrationResourceData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationSyncJobResource extends JsonResource
{
    use FormatsIntegrationResourceData;

    public function toArray(Request $request): array
    {
        $monitoringState = $this->status === 'completed' && $this->retried_at !== null
            ? 'retried'
            : $this->status;

        return [
            'id' => $this->id,
            'job_uuid' => $this->job_uuid,
            'version' => $this->version,
            'system_key' => $this->system_key,
            'event_key' => $this->event_key,
            'direction' => $this->direction,
            'status' => $this->status,
            'monitoring_state' => $monitoringState,
            'trigger_source' => $this->trigger_source,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'request_payload' => $this->request_payload ?? [],
            'response_payload' => $this->response_payload ?? [],
            'request_headers' => $this->redactHeaders($this->request_headers),
            'response_headers' => $this->redactHeaders($this->response_headers),
            'attempts_count' => $this->attempts_count,
            'last_attempt_at' => $this->last_attempt_at?->toIso8601String(),
            'queued_at' => $this->queued_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'retried_at' => $this->retried_at?->toIso8601String(),
            'last_error' => $this->last_error,
            'can_retry' => $this->status === 'failed',
            'connection' => $this->whenLoaded('connection', fn (): array => [
                'id' => $this->connection?->id,
                'system_key' => $this->connection?->system_key,
                'name' => $this->connection?->name,
                'status' => $this->connection?->status,
            ]),
            'subscription' => $this->whenLoaded('subscription', fn (): array => [
                'id' => $this->subscription?->id,
                'subscription_key' => $this->subscription?->subscription_key,
                'event_key' => $this->subscription?->event_key,
                'direction' => $this->subscription?->direction,
                'status' => $this->subscription?->status,
            ]),
            'errors' => $this->whenLoaded('errors', fn () => $this->errors->map(
                fn (IntegrationSyncError $error): array => [
                    'id' => $error->id,
                    'attempt_number' => $error->attempt_number,
                    'error_code' => $error->error_code,
                    'error_message' => $error->error_message,
                    'request_payload' => $error->request_payload ?? [],
                    'response_payload' => $error->response_payload ?? [],
                    'request_headers' => $this->redactHeaders($error->request_headers),
                    'response_headers' => $this->redactHeaders($error->response_headers),
                    'occurred_at' => $error->occurred_at?->toIso8601String(),
                    'resolved_at' => $error->resolved_at?->toIso8601String(),
                ],
            )),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
