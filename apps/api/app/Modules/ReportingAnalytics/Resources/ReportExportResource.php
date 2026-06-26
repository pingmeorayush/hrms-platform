<?php

namespace App\Modules\ReportingAnalytics\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isExpired = $this->status === 'expired'
            || ($this->retention_expires_at !== null && $this->retention_expires_at->isPast());

        return [
            'id' => $this->id,
            'export_uuid' => $this->export_uuid,
            'status' => $this->status,
            'format' => $this->format,
            'execution_mode' => $this->execution_mode,
            'delivery_target' => $this->delivery_target,
            'dataset' => $this->whenLoaded('reportDataset', fn (): array => [
                'id' => $this->reportDataset?->id,
                'key' => $this->reportDataset?->key,
                'name' => $this->reportDataset?->name,
                'domain' => $this->reportDataset?->domain,
            ]),
            'requested_by' => $this->whenLoaded(
                'requestedBy',
                fn () => new UserReferenceResource($this->requestedBy),
            ),
            'query' => [
                'filters' => $this->requested_filters ?? [],
                'filter_operators' => $this->requested_filter_operators ?? [],
                'sort_by' => $this->sort_by,
                'sort_direction' => $this->sort_direction,
                'drilldown_path' => $this->drilldown_path,
            ],
            'counts' => [
                'estimated_row_count' => $this->estimated_row_count,
                'exported_row_count' => $this->exported_row_count,
            ],
            'visibility' => $this->visibility_posture ?? [
                'masked_field_keys' => [],
                'hidden_field_keys' => [],
                'drilldown_keys' => [],
            ],
            'freshness' => $this->freshness_snapshot ?? [
                'generated_at' => null,
                'expectation_minutes' => null,
            ],
            'file' => [
                'name' => $this->file_name,
                'size_bytes' => $this->file_size_bytes,
                'checksum_sha256' => $this->checksum_sha256,
                'download_available' => $this->status === 'completed' && ! $isExpired,
                'download_url' => $this->status === 'completed' && ! $isExpired
                    ? route('reporting.exports.download', ['reportExportId' => $this->id])
                    : null,
            ],
            'retention' => [
                'expires_at' => $this->retention_expires_at?->toIso8601String(),
                'is_expired' => $isExpired,
            ],
            'requested_at' => $this->requested_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'notified_at' => $this->notified_at?->toIso8601String(),
            'last_error' => $this->last_error,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
