<?php

namespace App\Modules\PerformanceManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceReviewCycleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'cycle_type' => $this->cycle_type,
            'starts_on' => $this->starts_on?->toDateString(),
            'ends_on' => $this->ends_on?->toDateString(),
            'self_review_due_on' => $this->self_review_due_on?->toDateString(),
            'manager_review_due_on' => $this->manager_review_due_on?->toDateString(),
            'calibration_starts_on' => $this->calibration_starts_on?->toDateString(),
            'calibration_ends_on' => $this->calibration_ends_on?->toDateString(),
            'publish_on' => $this->publish_on?->toDateString(),
            'participant_rules' => $this->participant_rules,
            'review_template' => $this->review_template,
            'competency_visibility' => $this->competency_visibility,
            'status' => $this->status,
            'goal_count' => $this->whenCounted('goals'),
            'can_edit_configuration' => in_array($this->status, ['draft', 'scheduled', 'active'], true),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
