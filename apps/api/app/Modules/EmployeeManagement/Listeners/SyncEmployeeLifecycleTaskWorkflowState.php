<?php

namespace App\Modules\EmployeeManagement\Listeners;

use App\Models\EmployeeOnboardingTask;
use App\Models\User;
use App\Models\WorkflowTask;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;
use Illuminate\Support\Facades\DB;

class SyncEmployeeLifecycleTaskWorkflowState
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(WorkflowInstanceTransitioned $event): void
    {
        if ($event->instance->reference_type !== 'employee_lifecycle_task') {
            return;
        }

        $task = EmployeeOnboardingTask::query()
            ->with(['employee', 'workflowInstance.tasks.actor'])
            ->find($event->instance->reference_id);

        if (! $task) {
            return;
        }

        $decisionTask = WorkflowTask::query()
            ->with('actor')
            ->where('workflow_instance_id', $task->workflow_instance_id)
            ->whereNotNull('acted_at')
            ->orderByDesc('acted_at')
            ->orderByDesc('id')
            ->first();

        $actor = $decisionTask?->actor;
        $comment = $decisionTask?->decision_comment;

        match ($event->transition) {
            'completed' => $this->markApproved($task, $actor, $comment),
            'reject' => $this->markRejected($task, $actor, $comment),
            'request_changes' => $this->markChangesRequested($task, $actor, $comment),
            default => null,
        };
    }

    private function markApproved(EmployeeOnboardingTask $task, ?User $actor, ?string $comment): void
    {
        DB::transaction(function () use ($task, $actor, $comment): void {
            $task->forceFill([
                'status' => 'completed',
                'approved_at' => now(),
                'completed_at' => $task->completed_at ?? now(),
                'completed_by_user_id' => $task->completed_by_user_id ?? $actor?->id,
                'latest_action_by_user_id' => $actor?->id,
                'updated_by_user_id' => $actor?->id,
                'notes' => $comment ?: $task->notes,
            ])->save();

            $this->auditLogger->record(
                eventType: 'employee.lifecycle_task.approved',
                actor: $actor,
                metadata: [
                    'employee_id' => $task->employee_id,
                    'task_id' => $task->id,
                    'workflow_instance_id' => $task->workflow_instance_id,
                    'lifecycle_type' => $task->lifecycle_type,
                ],
                entityType: 'employee_lifecycle_task',
                entityId: (string) $task->id,
            );
        });
    }

    private function markRejected(EmployeeOnboardingTask $task, ?User $actor, ?string $comment): void
    {
        $task->forceFill([
            'status' => 'rejected',
            'latest_action_by_user_id' => $actor?->id,
            'updated_by_user_id' => $actor?->id,
            'notes' => $comment ?: $task->notes,
        ])->save();

        $this->auditLogger->record(
            eventType: 'employee.lifecycle_task.rejected',
            actor: $actor,
            metadata: [
                'employee_id' => $task->employee_id,
                'task_id' => $task->id,
                'workflow_instance_id' => $task->workflow_instance_id,
                'lifecycle_type' => $task->lifecycle_type,
            ],
            entityType: 'employee_lifecycle_task',
            entityId: (string) $task->id,
        );
    }

    private function markChangesRequested(EmployeeOnboardingTask $task, ?User $actor, ?string $comment): void
    {
        $task->forceFill([
            'status' => 'changes_requested',
            'latest_action_by_user_id' => $actor?->id,
            'updated_by_user_id' => $actor?->id,
            'notes' => $comment ?: $task->notes,
        ])->save();

        $this->auditLogger->record(
            eventType: 'employee.lifecycle_task.changes_requested',
            actor: $actor,
            metadata: [
                'employee_id' => $task->employee_id,
                'task_id' => $task->id,
                'workflow_instance_id' => $task->workflow_instance_id,
                'lifecycle_type' => $task->lifecycle_type,
            ],
            entityType: 'employee_lifecycle_task',
            entityId: (string) $task->id,
        );
    }
}
