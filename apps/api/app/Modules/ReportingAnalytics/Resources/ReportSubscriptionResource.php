<?php

namespace App\Modules\ReportingAnalytics\Resources;

use App\Modules\Platform\Auth\Resources\UserReferenceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_uuid' => $this->subscription_uuid,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'owner' => $this->whenLoaded(
                'owner',
                fn () => new UserReferenceResource($this->owner),
            ),
            'source' => [
                'dataset' => $this->whenLoaded('reportDataset', fn (): ?array => $this->reportDataset
                    ? [
                        'id' => $this->reportDataset->id,
                        'key' => $this->reportDataset->key,
                        'name' => $this->reportDataset->name,
                        'domain' => $this->reportDataset->domain,
                    ]
                    : null),
                'saved_view' => $this->whenLoaded('savedReportView', fn (): ?array => $this->savedReportView
                    ? [
                        'id' => $this->savedReportView->id,
                        'view_uuid' => $this->savedReportView->view_uuid,
                        'name' => $this->savedReportView->name,
                        'status' => $this->savedReportView->status,
                    ]
                    : null),
            ],
            'delivery' => [
                'channel' => $this->delivery_channel,
                'target' => $this->delivery_target,
                'export_format' => $this->export_format,
            ],
            'schedule' => [
                'frequency' => $this->frequency,
                'timezone' => $this->timezone,
                'config' => $this->schedule_config ?? [],
                'next_delivery_at' => $this->next_delivery_at?->toIso8601String(),
            ],
            'query' => [
                'filters' => $this->filters ?? [],
                'filter_operators' => $this->filter_operators ?? [],
                'sort_by' => $this->sort_by,
                'sort_direction' => $this->sort_direction,
                'drilldown_path' => $this->drilldown_path,
            ],
            'validation' => $this->validation_state ?? [
                'status' => 'unknown',
                'reason' => null,
            ],
            'last_delivery' => [
                'status' => $this->last_delivery_status,
                'error' => $this->last_delivery_error,
                'delivered_at' => $this->last_delivered_at?->toIso8601String(),
                'report_export_id' => $this->last_report_export_id,
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
