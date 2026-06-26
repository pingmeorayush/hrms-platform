<?php

namespace App\Modules\LearningManagement\Resources;

use App\Modules\LearningManagement\Services\LearningTrackingStateResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var LearningTrackingStateResolver $trackingStateResolver */
        $trackingStateResolver = app(LearningTrackingStateResolver::class);
        $actor = $request->user();
        $isPrivileged = $actor && ($actor->can('learning.manage') || $actor->can('learning.assign'));

        $targetSummary = null;
        $targetCount = $this->target_count;
        $completionCount = $this->completion_count;

        if ($this->relationLoaded('targets')) {
            if (! $isPrivileged) {
                $targetCount = $this->targets->count();
                $completionCount = $this->targets->where('status', 'completed')->count();
            }

            $targetSummary = [
                'total_count' => $targetCount,
                'completed_count' => $completionCount,
                'overdue_count' => $this->targets
                    ->filter(fn ($target) => $trackingStateResolver->summarizeTarget($target)['due_state'] === 'overdue')
                    ->count(),
                'renewal_overdue_count' => $this->targets
                    ->filter(fn ($target) => $trackingStateResolver->summarizeTarget($target)['renewal_posture'] === 'overdue')
                    ->count(),
            ];
        }

        return [
            'id' => $this->id,
            'assignment_code' => $this->assignment_code,
            'item' => new LearningItemResource($this->whenLoaded('item')),
            'audience_type' => $this->audience_type,
            'audience_rules' => $this->audience_rules,
            'assigned_on' => $this->assigned_on?->toDateString(),
            'due_on' => $this->due_on?->toDateString(),
            'completion_rules' => $this->completion_rules,
            'notes' => $this->notes,
            'status' => $this->status,
            'target_count' => $targetCount,
            'completion_count' => $completionCount,
            'assigned_by' => $this->whenLoaded('assignedBy', fn (): array => [
                'id' => $this->assignedBy?->id,
                'name' => $this->assignedBy?->name,
            ]),
            'target_summary' => $targetSummary,
            'targets' => LearningAssignmentTargetResource::collection($this->whenLoaded('targets')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
