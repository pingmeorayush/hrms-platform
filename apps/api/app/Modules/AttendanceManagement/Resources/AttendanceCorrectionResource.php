<?php

namespace App\Modules\AttendanceManagement\Resources;

use App\Models\WorkflowTask;
use App\Modules\Platform\Workflow\Resources\WorkflowTaskResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceCorrectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tasks = $this->whenLoaded('workflowInstance', function () {
            return $this->workflowInstance?->tasks
                ?->sortBy('sequence')
                ->values();
        });

        return [
            'id' => $this->id,
            'status' => $this->status,
            'reason' => $this->reason,
            'attendance_record_id' => $this->attendance_record_id,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee?->id,
                'employee_code' => $this->employee?->employee_code,
                'full_name' => $this->employee?->full_name,
            ]),
            'requested_by' => $this->whenLoaded('requester', fn () => [
                'id' => $this->requester?->id,
                'name' => $this->requester?->name,
                'email' => $this->requester?->email,
            ]),
            'latest_action_by' => $this->whenLoaded('latestActor', fn () => [
                'id' => $this->latestActor?->id,
                'name' => $this->latestActor?->name,
                'email' => $this->latestActor?->email,
            ]),
            'original_values' => $this->original_values ?? [],
            'corrected_values' => $this->corrected_values ?? [],
            'applied_values' => $this->applied_values,
            'decision_comment' => $this->decision_comment,
            'workflow' => $this->whenLoaded('workflowInstance', fn () => [
                'id' => $this->workflowInstance?->id,
                'status' => $this->workflowInstance?->status,
                'current_stage_sequence' => $this->workflowInstance?->current_stage_sequence,
                'approval_history' => WorkflowTaskResource::collection($tasks),
                'current_task' => $this->workflowInstance?->tasks
                    ?->where('status', 'open')
                    ->sortBy('sequence')
                    ->map(fn (WorkflowTask $task) => (new WorkflowTaskResource($task))->resolve())
                    ->first(),
            ]),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
