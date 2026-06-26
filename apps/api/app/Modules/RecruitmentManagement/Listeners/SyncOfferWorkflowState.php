<?php

namespace App\Modules\RecruitmentManagement\Listeners;

use App\Models\Offer;
use App\Models\OfferDecision;
use App\Models\User;
use App\Models\WorkflowTask;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Notifications\Services\NotificationService;
use App\Modules\Platform\Workflow\Events\WorkflowInstanceTransitioned;
use Illuminate\Support\Facades\DB;

class SyncOfferWorkflowState
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly NotificationService $notificationService,
    ) {}

    public function handle(WorkflowInstanceTransitioned $event): void
    {
        if ($event->instance->reference_type !== 'offer') {
            return;
        }

        $offer = Offer::query()
            ->with(['workflowInstance.tasks.actor', 'recruiter', 'requisition.hiringManager.user'])
            ->find($event->instance->reference_id);

        if (! $offer) {
            return;
        }

        $decisionTask = WorkflowTask::query()
            ->with('actor')
            ->where('workflow_instance_id', $offer->workflow_instance_id)
            ->whereNotNull('acted_at')
            ->orderByDesc('acted_at')
            ->orderByDesc('id')
            ->first();

        $actor = $decisionTask?->actor;
        $comment = $decisionTask?->decision_comment;

        match ($event->transition) {
            'completed' => $this->markApproved($offer, $actor),
            'reject' => $this->markRejected($offer, $actor, $comment),
            'request_changes' => $this->markChangesRequested($offer, $actor, $comment),
            default => null,
        };
    }

    private function markApproved(Offer $offer, ?User $actor): void
    {
        DB::transaction(function () use ($offer, $actor): void {
            $fromStatus = $offer->status;

            $offer->forceFill([
                'status' => 'approved',
                'approved_at' => now(),
                'updated_by_user_id' => $actor?->id,
            ])->save();

            $this->recordDecision($offer, 'workflow_approved', $fromStatus, 'approved', 'Offer approved through workflow.', $actor);

            $this->auditLogger->record(
                eventType: 'recruitment.offer.approved',
                actor: $actor,
                metadata: [
                    'offer_id' => $offer->id,
                    'workflow_instance_id' => $offer->workflow_instance_id,
                    'candidate_id' => $offer->candidate_id,
                ],
                entityType: 'offer',
                entityId: (string) $offer->id,
            );

            $this->notifyStakeholders($offer, $actor, 'Offer approved', 'The offer is now approved and ready to send.');
        });
    }

    private function markRejected(Offer $offer, ?User $actor, ?string $comment): void
    {
        $fromStatus = $offer->status;

        $offer->forceFill([
            'status' => 'rejected',
            'approved_at' => null,
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $this->recordDecision($offer, 'workflow_rejected', $fromStatus, 'rejected', $comment, $actor);

        $this->auditLogger->record(
            eventType: 'recruitment.offer.rejected',
            actor: $actor,
            metadata: [
                'offer_id' => $offer->id,
                'workflow_instance_id' => $offer->workflow_instance_id,
                'candidate_id' => $offer->candidate_id,
                'comment' => $comment,
            ],
            entityType: 'offer',
            entityId: (string) $offer->id,
        );

        $this->notifyStakeholders($offer, $actor, 'Offer rejected', 'The offer was rejected during approval.');
    }

    private function markChangesRequested(Offer $offer, ?User $actor, ?string $comment): void
    {
        $fromStatus = $offer->status;

        $offer->forceFill([
            'status' => 'changes_requested',
            'approved_at' => null,
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $this->recordDecision($offer, 'workflow_changes_requested', $fromStatus, 'changes_requested', $comment, $actor);

        $this->auditLogger->record(
            eventType: 'recruitment.offer.changes_requested',
            actor: $actor,
            metadata: [
                'offer_id' => $offer->id,
                'workflow_instance_id' => $offer->workflow_instance_id,
                'candidate_id' => $offer->candidate_id,
                'comment' => $comment,
            ],
            entityType: 'offer',
            entityId: (string) $offer->id,
        );

        $this->notifyStakeholders($offer, $actor, 'Offer needs changes', 'The offer needs changes before approval can continue.');
    }

    private function recordDecision(
        Offer $offer,
        string $decisionType,
        ?string $fromStatus,
        string $toStatus,
        ?string $comment,
        ?User $actor,
    ): void {
        OfferDecision::query()->create([
            'company_id' => $offer->company_id,
            'offer_id' => $offer->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'decision_type' => $decisionType,
            'comment' => $comment,
            'acted_by_user_id' => $actor?->id,
            'acted_at' => now(),
        ]);
    }

    private function notifyStakeholders(Offer $offer, ?User $actor, string $title, string $message): void
    {
        $recipients = collect([
            $offer->recruiter,
            $offer->requisition?->hiringManager?->user,
        ])->filter(fn (?User $user) => $user?->is_active)->unique('id');

        foreach ($recipients as $recipient) {
            if ($recipient->id === $actor?->id) {
                continue;
            }

            $this->notificationService->sendDirect($recipient, [
                'type' => 'recruitment',
                'channel' => 'in_app',
                'title' => $title,
                'message' => $message,
                'priority' => 'normal',
                'deep_link' => '/recruitment/offers/'.$offer->id,
                'data' => [
                    'offer_id' => $offer->id,
                    'candidate_id' => $offer->candidate_id,
                    'job_requisition_id' => $offer->job_requisition_id,
                    'status' => $offer->status,
                ],
            ], $actor);
        }
    }
}
