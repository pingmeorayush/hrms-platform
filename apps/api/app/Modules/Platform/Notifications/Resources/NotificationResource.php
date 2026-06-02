<?php

namespace App\Modules\Platform\Notifications\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'channel' => $this->channel,
            'title' => $this->title,
            'message' => $this->message,
            'priority' => $this->priority,
            'status' => $this->status,
            'delivery_status' => $this->delivery_status,
            'deep_link' => $this->deep_link,
            'retry_count' => $this->retry_count,
            'last_error' => $this->last_error,
            'data' => $this->data ?? [],
            'read_at' => $this->read_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
