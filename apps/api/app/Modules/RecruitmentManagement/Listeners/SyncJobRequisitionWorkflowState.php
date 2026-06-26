<?php

namespace App\Modules\RecruitmentManagement\Listeners;

use App\Models\JobRequisition;
use App\Models\User;
use App\Models\WorkflowTask;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;
use Illuminate\Support\Facades\DB;

class SyncJobRequisitionWorkflowState
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(WorkflowInstanceTransitioned $event): void
    {
        if ($event->instance->reference_type !== 'job_requisition') {
            return;
        }

        $requisition = JobRequisition::query()
            ->with(['workflowInstance.tasks.actor', 'requestedBy'])
            ->find($event->instance->reference_id);

        if (! $requisition) {
            return;
        }

        $decisionTask = WorkflowTask::query()
            ->with('actor')
            ->where('workflow_instance_id', $requisition->workflow_instance_id)
            ->whereNotNull('acted_at')
            ->orderByDesc('acted_at')
            ->orderByDesc('id')
            ->first();

        $actor = $decisionTask?->actor;
        $comment = $decisionTask?->decision_comment;

        match ($event->transition) {
            'completed' => $this->markApproved($requisition, $actor),
            'reject' => $this->markRejected($requisition, $actor, $comment),
            'request_changes' => $this->markChangesRequested($requisition, $actor, $comment),
            default => null,
        };
    }

    private function markApproved(JobRequisition $requisition, ?User $actor): void
    {
        DB::transaction(function () use ($requisition, $actor): void {
            $requisition->forceFill([
                'status' => 'approved',
                'status_before_hold' => null,
                'approved_at' => now(),
                'updated_by_user_id' => $actor?->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'recruitment.requisition.approved',
                actor: $actor,
                metadata: [
                    'job_requisition_id' => $requisition->id,
                    'workflow_instance_id' => $requisition->workflow_instance_id,
                ],
                entityType: 'job_requisition',
                entityId: (string) $requisition->id,
            );
        });
    }

    private function markRejected(JobRequisition $requisition, ?User $actor, ?string $comment): void
    {
        $requisition->forceFill([
            'status' => 'rejected',
            'status_before_hold' => null,
            'approved_at' => null,
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'recruitment.requisition.rejected',
            actor: $actor,
            metadata: [
                'job_requisition_id' => $requisition->id,
                'workflow_instance_id' => $requisition->workflow_instance_id,
                'comment' => $comment,
            ],
            entityType: 'job_requisition',
            entityId: (string) $requisition->id,
        );
    }

    private function markChangesRequested(JobRequisition $requisition, ?User $actor, ?string $comment): void
    {
        $requisition->forceFill([
            'status' => 'changes_requested',
            'status_before_hold' => null,
            'approved_at' => null,
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'recruitment.requisition.changes_requested',
            actor: $actor,
            metadata: [
                'job_requisition_id' => $requisition->id,
                'workflow_instance_id' => $requisition->workflow_instance_id,
                'comment' => $comment,
            ],
            entityType: 'job_requisition',
            entityId: (string) $requisition->id,
        );
    }
}
