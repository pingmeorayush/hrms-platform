<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Candidate;
use App\Models\Offer;
use App\Models\OfferDecision;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Notifications\Services\NotificationService;
use App\Modules\Platform\Workflow\Services\WorkflowService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OfferService
{
    public function __construct(
        private readonly OfferAccessScopeService $accessScopeService,
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowService $workflowService,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Offer>
     */
    public function search(User $actor, array $filters): LengthAwarePaginator
    {
        $results = $this->accessScopeService
            ->offersQuery($actor)
            ->when(
                array_key_exists('job_requisition_id', $filters),
                fn (Builder $builder) => $builder->where('job_requisition_id', $filters['job_requisition_id']),
            )
            ->when(
                array_key_exists('candidate_id', $filters),
                fn (Builder $builder) => $builder->where('candidate_id', $filters['candidate_id']),
            )
            ->when(
                array_key_exists('recruiter_user_id', $filters),
                fn (Builder $builder) => $builder->where('recruiter_user_id', $filters['recruiter_user_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('expires_on_from', $filters),
                fn (Builder $builder) => $builder->whereDate('expires_on', '>=', $filters['expires_on_from']),
            )
            ->when(
                array_key_exists('expires_on_to', $filters),
                fn (Builder $builder) => $builder->whereDate('expires_on', '<=', $filters['expires_on_to']),
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);

        $this->auditLogger->record(
            eventType: 'recruitment.offer.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'offer_count' => $results->total(),
            ],
            entityType: 'offer',
            entityId: null,
        );

        return $results;
    }

    public function findForView(User $actor, int $offerId): Offer
    {
        $offer = $this->accessScopeService->resolveAccessibleOffer($actor, $offerId);

        $this->auditLogger->record(
            eventType: 'recruitment.offer.viewed',
            actor: $actor,
            metadata: [
                'offer_id' => $offer->id,
                'candidate_id' => $offer->candidate_id,
                'job_requisition_id' => $offer->job_requisition_id,
                'status' => $offer->status,
            ],
            entityType: 'offer',
            entityId: (string) $offer->id,
        );

        return $offer;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(User $actor, array $payload): Offer
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'offer' => ['Creating offers requires recruitment management permission.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $payload): Offer {
            $candidate = $this->resolveEligibleCandidate((int) $payload['candidate_id']);

            if ($candidate->job_requisition_id !== (int) $payload['job_requisition_id']) {
                throw ValidationException::withMessages([
                    'candidate_id' => ['The selected candidate does not belong to the provided requisition.'],
                ]);
            }

            $this->assertNoActiveOffer($candidate->id);

            $offer = Offer::query()->create([
                'company_id' => $actor->company_id,
                'job_requisition_id' => $candidate->job_requisition_id,
                'candidate_id' => $candidate->id,
                'recruiter_user_id' => $payload['recruiter_user_id'] ?? $candidate->recruiter_user_id,
                'requested_by_user_id' => $actor->id,
                'workflow_instance_id' => null,
                'offer_code' => $this->resolveOfferCode(),
                'status' => 'draft',
                'employment_type' => $payload['employment_type'],
                'currency' => Str::upper((string) $payload['currency']),
                'annual_ctc_amount' => (float) $payload['annual_ctc_amount'],
                'joining_bonus_amount' => $payload['joining_bonus_amount'] ?? null,
                'proposed_start_date' => $payload['proposed_start_date'] ?? null,
                'expires_on' => $payload['expires_on'],
                'notes' => $payload['notes'] ?? null,
                'candidate_message' => $payload['candidate_message'] ?? null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->recordDecision($offer, 'offer_created', null, 'draft', 'Offer draft created.', $actor);
            $this->moveCandidateToOfferStage($candidate, $actor);

            $this->auditLogger->record(
                eventType: 'recruitment.offer.created',
                actor: $actor,
                metadata: [
                    'offer_id' => $offer->id,
                    'candidate_id' => $offer->candidate_id,
                    'job_requisition_id' => $offer->job_requisition_id,
                    'offer_code' => $offer->offer_code,
                    'annual_ctc_amount' => $offer->annual_ctc_amount,
                    'expires_on' => $offer->expires_on?->toDateString(),
                ],
                entityType: 'offer',
                entityId: (string) $offer->id,
            );

            return $this->loadOffer($offer);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(User $actor, int $offerId, array $payload): Offer
    {
        $offer = $this->accessScopeService->resolveAccessibleOffer($actor, $offerId);
        $action = $payload['action'] ?? null;

        if ($action) {
            return match ($action) {
                'submit' => $this->submit($actor, $offer),
                'approve', 'reject', 'request_changes' => $this->actOnWorkflowTask($actor, $offer, $action, $payload['comment'] ?? null),
                'mark_sent' => $this->markSent($actor, $offer, $payload['comment'] ?? null),
                'record_acceptance' => $this->recordCandidateDecision($actor, $offer, 'accepted', $payload['comment'] ?? null),
                'record_decline' => $this->recordCandidateDecision($actor, $offer, 'declined', $payload['comment'] ?? null),
                'mark_expired' => $this->markExpired($actor, $offer, $payload['comment'] ?? null),
                default => throw ValidationException::withMessages([
                    'action' => ['The requested offer action is not supported.'],
                ]),
            };
        }

        return $this->updateDetails($actor, $offer, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function updateDetails(User $actor, Offer $offer, array $payload): Offer
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'offer' => ['Updating offer details requires recruitment management permission.'],
            ]);
        }

        if (! in_array($offer->status, ['draft', 'rejected', 'changes_requested'], true)) {
            throw ValidationException::withMessages([
                'offer' => ['Only draft, rejected, or changes-requested offers can be edited directly.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $offer, $payload): Offer {
            $before = $offer->only([
                'recruiter_user_id',
                'employment_type',
                'currency',
                'annual_ctc_amount',
                'joining_bonus_amount',
                'proposed_start_date',
                'expires_on',
            ]);

            $offer->fill([
                'recruiter_user_id' => array_key_exists('recruiter_user_id', $payload) ? $payload['recruiter_user_id'] : $offer->recruiter_user_id,
                'employment_type' => $payload['employment_type'] ?? $offer->employment_type,
                'currency' => array_key_exists('currency', $payload) ? Str::upper((string) $payload['currency']) : $offer->currency,
                'annual_ctc_amount' => $payload['annual_ctc_amount'] ?? $offer->annual_ctc_amount,
                'joining_bonus_amount' => array_key_exists('joining_bonus_amount', $payload) ? $payload['joining_bonus_amount'] : $offer->joining_bonus_amount,
                'proposed_start_date' => array_key_exists('proposed_start_date', $payload) ? $payload['proposed_start_date'] : $offer->proposed_start_date,
                'expires_on' => array_key_exists('expires_on', $payload) ? $payload['expires_on'] : $offer->expires_on,
                'notes' => array_key_exists('notes', $payload) ? $payload['notes'] : $offer->notes,
                'candidate_message' => array_key_exists('candidate_message', $payload) ? $payload['candidate_message'] : $offer->candidate_message,
                'updated_by_user_id' => $actor->id,
            ]);
            $offer->save();

            $this->auditLogger->record(
                eventType: 'recruitment.offer.updated',
                actor: $actor,
                metadata: [
                    'offer_id' => $offer->id,
                    'before' => $before,
                    'after' => $offer->only([
                        'recruiter_user_id',
                        'employment_type',
                        'currency',
                        'annual_ctc_amount',
                        'joining_bonus_amount',
                        'proposed_start_date',
                        'expires_on',
                    ]),
                ],
                entityType: 'offer',
                entityId: (string) $offer->id,
            );

            return $this->loadOffer($offer);
        });
    }

    private function submit(User $actor, Offer $offer): Offer
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'offer' => ['Submitting an offer requires recruitment management permission.'],
            ]);
        }

        if (! in_array($offer->status, ['draft', 'rejected', 'changes_requested'], true)) {
            throw ValidationException::withMessages([
                'offer' => ['Only draft, rejected, or changes-requested offers can be submitted for approval.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $offer): Offer {
            $candidate = $offer->candidate()->with('requisition.hiringManager.user')->firstOrFail();
            $managerUser = $candidate->requisition->hiringManager?->user;

            if (! $managerUser?->is_active) {
                throw ValidationException::withMessages([
                    'offer' => ['The requisition hiring manager must be linked to an active user account before offer approval can begin.'],
                ]);
            }

            $workflowInstance = $this->workflowService->startInstance($actor, [
                'workflow_key' => 'recruitment-offer-approval',
                'reference_type' => 'offer',
                'reference_id' => (string) $offer->id,
                'payload' => [
                    'offer_id' => $offer->id,
                    'candidate_id' => $offer->candidate_id,
                    'job_requisition_id' => $offer->job_requisition_id,
                    'hiring_manager_user_id' => $managerUser->id,
                    'recruiter_user_id' => $offer->recruiter_user_id,
                ],
            ]);

            $fromStatus = $offer->status;

            $offer->forceFill([
                'workflow_instance_id' => $workflowInstance->id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->recordDecision($offer, 'submitted_for_approval', $fromStatus, 'submitted', 'Offer submitted for approval.', $actor);

            $this->auditLogger->record(
                eventType: 'recruitment.offer.submitted',
                actor: $actor,
                metadata: [
                    'offer_id' => $offer->id,
                    'workflow_instance_id' => $workflowInstance->id,
                    'candidate_id' => $offer->candidate_id,
                ],
                entityType: 'offer',
                entityId: (string) $offer->id,
            );

            return $this->loadOffer($offer);
        });
    }

    private function actOnWorkflowTask(User $actor, Offer $offer, string $action, ?string $comment): Offer
    {
        if (! $actor->can('recruitment.approve') && ! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'offer' => ['Approving or rejecting an offer requires recruitment approval permission.'],
            ]);
        }

        if (! $offer->workflow_instance_id) {
            throw ValidationException::withMessages([
                'offer' => ['This offer is not linked to an approval workflow.'],
            ]);
        }

        $task = $offer->workflowInstance?->tasks()
            ->where('status', 'open')
            ->orderBy('sequence')
            ->first();

        if (! $task) {
            throw ValidationException::withMessages([
                'offer' => ['No open workflow task is available for this offer.'],
            ]);
        }

        $this->workflowService->decideTask($task, $actor, [
            'action' => $action,
            'comment' => $comment,
        ]);

        return $this->loadOffer($this->accessScopeService->resolveAccessibleOffer($actor, $offer->id));
    }

    private function markSent(User $actor, Offer $offer, ?string $comment): Offer
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'offer' => ['Marking an offer as sent requires recruitment management permission.'],
            ]);
        }

        if ($offer->status !== 'approved') {
            throw ValidationException::withMessages([
                'offer' => ['Only approved offers can be marked as sent.'],
            ]);
        }

        $fromStatus = $offer->status;

        $offer->forceFill([
            'status' => 'sent',
            'sent_at' => now(),
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->recordDecision($offer, 'sent', $fromStatus, 'sent', $comment ?: 'Offer shared with candidate.', $actor);

        $this->notifyOfferStakeholders($offer, $actor, 'Offer sent', 'The offer has been sent to the candidate for decision.');

        return $this->loadOffer($offer);
    }

    private function recordCandidateDecision(User $actor, Offer $offer, string $decision, ?string $comment): Offer
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'offer' => ['Recording candidate decisions requires recruitment management permission.'],
            ]);
        }

        if (! in_array($offer->status, ['approved', 'sent'], true)) {
            throw ValidationException::withMessages([
                'offer' => ['Candidate decisions can only be recorded after offer approval or send-out.'],
            ]);
        }

        if ($offer->expires_on && $offer->expires_on->isPast()) {
            throw ValidationException::withMessages([
                'offer' => ['This offer has crossed its expiry date. Mark it expired before recording a final candidate decision.'],
            ]);
        }

        if ($decision === 'declined' && blank($comment)) {
            throw ValidationException::withMessages([
                'comment' => ['A decline reason is required when recording candidate decline.'],
            ]);
        }

        $fromStatus = $offer->status;

        $offer->forceFill([
            'status' => $decision,
            'accepted_at' => $decision === 'accepted' ? now() : null,
            'declined_at' => $decision === 'declined' ? now() : null,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->recordDecision($offer, 'candidate_'.$decision, $fromStatus, $decision, $comment, $actor);

        $this->notifyOfferStakeholders(
            $offer,
            $actor,
            $decision === 'accepted' ? 'Offer accepted' : 'Offer declined',
            $decision === 'accepted'
                ? 'The candidate has accepted the offer.'
                : 'The candidate has declined the offer.',
        );

        return $this->loadOffer($offer);
    }

    private function markExpired(User $actor, Offer $offer, ?string $comment): Offer
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'offer' => ['Marking an offer expired requires recruitment management permission.'],
            ]);
        }

        if (! in_array($offer->status, ['approved', 'sent'], true)) {
            throw ValidationException::withMessages([
                'offer' => ['Only approved or sent offers can be marked expired.'],
            ]);
        }

        if (! $offer->expires_on || ! $offer->expires_on->isPast()) {
            throw ValidationException::withMessages([
                'offer' => ['Offers can only be marked expired after the configured expiry date has passed.'],
            ]);
        }

        $fromStatus = $offer->status;

        $offer->forceFill([
            'status' => 'expired',
            'expired_at' => now(),
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->recordDecision($offer, 'expired', $fromStatus, 'expired', $comment, $actor);

        $this->notifyOfferStakeholders($offer, $actor, 'Offer expired', 'The offer expired without an accepted candidate response.');

        return $this->loadOffer($offer);
    }

    private function resolveEligibleCandidate(int $candidateId): Candidate
    {
        $candidate = Candidate::query()->with('requisition.hiringManager.user')->findOrFail($candidateId);

        if (! in_array($candidate->current_stage, ['offer', 'shortlisted', 'interview'], true)) {
            throw ValidationException::withMessages([
                'candidate_id' => ['Offers can only be created for shortlisted, interview-stage, or offer-stage candidates in the current recruitment baseline.'],
            ]);
        }

        if (in_array($candidate->status, ['rejected', 'withdrawn'], true)) {
            throw ValidationException::withMessages([
                'candidate_id' => ['Rejected or withdrawn candidates cannot receive offers.'],
            ]);
        }

        return $candidate;
    }

    private function assertNoActiveOffer(int $candidateId): void
    {
        $existingOffer = Offer::query()
            ->where('candidate_id', $candidateId)
            ->whereIn('status', ['draft', 'submitted', 'approved', 'changes_requested', 'sent'])
            ->first();

        if (! $existingOffer) {
            return;
        }

        throw ValidationException::withMessages([
            'candidate_id' => ['This candidate already has an active offer in progress (offer #'.$existingOffer->id.').'],
        ]);
    }

    private function moveCandidateToOfferStage(Candidate $candidate, User $actor): void
    {
        if ($candidate->current_stage === 'offer') {
            return;
        }

        $candidate->stageTransitions()->create([
            'company_id' => $candidate->company_id,
            'from_stage' => $candidate->current_stage,
            'to_stage' => 'offer',
            'resulting_status' => 'active',
            'comment' => 'Candidate entered offer preparation.',
            'transitioned_by_user_id' => $actor->id,
            'transitioned_at' => now(),
        ]);

        $candidate->forceFill([
            'current_stage' => 'offer',
            'status' => 'active',
            'stage_entered_at' => now(),
            'updated_by_user_id' => $actor->id,
        ])->save();
    }

    private function resolveOfferCode(): string
    {
        $nextOrdinal = ((int) Offer::query()->lockForUpdate()->max('id')) + 1;

        return sprintf('OFF-%04d', $nextOrdinal);
    }

    private function recordDecision(
        Offer $offer,
        string $decisionType,
        ?string $fromStatus,
        string $toStatus,
        ?string $comment,
        ?User $actor,
    ): OfferDecision {
        return OfferDecision::query()->create([
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

    private function notifyOfferStakeholders(Offer $offer, User $actor, string $title, string $message): void
    {
        $offer->loadMissing('recruiter', 'requisition.hiringManager.user');

        $recipients = collect([
            $offer->recruiter,
            $offer->requisition?->hiringManager?->user,
        ])->filter(fn (?User $user) => $user?->is_active)->unique('id');

        foreach ($recipients as $recipient) {
            if ($recipient->id === $actor->id) {
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

    private function loadOffer(Offer $offer): Offer
    {
        return $offer->load([
            'candidate.recruiter',
            'requisition',
            'recruiter',
            'requestedBy',
            'handoff.employee',
            'workflowInstance.definition',
            'workflowInstance.tasks.assignee',
            'workflowInstance.tasks.actor',
            'decisions.actor',
        ]);
    }
}
