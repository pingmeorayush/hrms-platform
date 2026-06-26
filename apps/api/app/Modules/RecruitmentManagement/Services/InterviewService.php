<?php

namespace App\Modules\RecruitmentManagement\Services;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\InterviewFeedback;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Notifications\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InterviewService
{
    public function __construct(
        private readonly InterviewAccessScopeService $accessScopeService,
        private readonly AuditLogger $auditLogger,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Interview>
     */
    public function search(User $actor, array $filters): LengthAwarePaginator
    {
        $results = $this->accessScopeService
            ->interviewsQuery($actor)
            ->when(
                array_key_exists('job_requisition_id', $filters),
                fn (Builder $builder) => $builder->where('job_requisition_id', $filters['job_requisition_id']),
            )
            ->when(
                array_key_exists('candidate_id', $filters),
                fn (Builder $builder) => $builder->where('candidate_id', $filters['candidate_id']),
            )
            ->when(
                array_key_exists('interviewer_user_id', $filters),
                fn (Builder $builder) => $builder->where('interviewer_user_id', $filters['interviewer_user_id']),
            )
            ->when(
                array_key_exists('status', $filters),
                fn (Builder $builder) => $builder->where('status', $filters['status']),
            )
            ->when(
                array_key_exists('date_from', $filters),
                fn (Builder $builder) => $builder->whereDate('scheduled_start_at', '>=', $filters['date_from']),
            )
            ->when(
                array_key_exists('date_to', $filters),
                fn (Builder $builder) => $builder->whereDate('scheduled_start_at', '<=', $filters['date_to']),
            )
            ->orderBy('scheduled_start_at')
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 15);

        $this->auditLogger->record(
            eventType: 'recruitment.interview.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'interview_count' => $results->total(),
            ],
            entityType: 'interview',
            entityId: null,
        );

        return $results;
    }

    public function findForView(User $actor, int $interviewId): Interview
    {
        return $this->accessScopeService->resolveAccessibleInterview($actor, $interviewId);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function schedule(User $actor, array $payload): Interview
    {
        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'interview' => ['Scheduling interviews requires recruitment management permission.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $payload): Interview {
            $candidate = Candidate::query()->findOrFail((int) $payload['candidate_id']);

            if ($candidate->job_requisition_id !== (int) $payload['job_requisition_id']) {
                throw ValidationException::withMessages([
                    'candidate_id' => ['The selected candidate does not belong to the provided requisition.'],
                ]);
            }

            if (in_array($candidate->status, ['rejected', 'withdrawn', 'hired'], true)) {
                throw ValidationException::withMessages([
                    'candidate_id' => ['Rejected, withdrawn, or already hired candidates cannot be scheduled for new interviews.'],
                ]);
            }

            $interviewer = User::query()->where('is_active', true)->findOrFail((int) $payload['interviewer_user_id']);
            $startAt = Carbon::parse((string) $payload['scheduled_start_at']);
            $endAt = Carbon::parse((string) $payload['scheduled_end_at']);

            $this->assertNoScheduleOverlap(
                companyId: (int) $actor->company_id,
                candidateId: $candidate->id,
                interviewerUserId: $interviewer->id,
                scheduledStartAt: $startAt,
                scheduledEndAt: $endAt,
            );

            $code = $this->resolveInterviewCode();

            $interview = Interview::query()->create([
                'company_id' => $actor->company_id,
                'job_requisition_id' => $candidate->job_requisition_id,
                'candidate_id' => $candidate->id,
                'interviewer_user_id' => $interviewer->id,
                'interview_code' => $code,
                'round_number' => (int) $payload['round_number'],
                'interview_type' => $payload['interview_type'],
                'status' => 'scheduled',
                'timezone' => $payload['timezone'],
                'scheduled_start_at' => $startAt,
                'scheduled_end_at' => $endAt,
                'meeting_mode' => $payload['meeting_mode'],
                'meeting_location' => $payload['meeting_location'] ?? null,
                'meeting_link' => $payload['meeting_link'] ?? null,
                'agenda' => $payload['agenda'] ?? null,
                'cancellation_reason' => null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'recruitment.interview.scheduled',
                actor: $actor,
                metadata: [
                    'interview_id' => $interview->id,
                    'candidate_id' => $candidate->id,
                    'job_requisition_id' => $candidate->job_requisition_id,
                    'interviewer_user_id' => $interviewer->id,
                    'round_number' => $interview->round_number,
                    'interview_type' => $interview->interview_type,
                ],
                entityType: 'interview',
                entityId: (string) $interview->id,
            );

            $this->moveCandidateToInterviewStage($candidate, $actor);

            $this->notificationService->sendDirect($interviewer, [
                'type' => 'recruitment',
                'channel' => 'in_app',
                'title' => 'Interview assigned',
                'message' => 'You have been assigned an interview for '.$candidate->first_name.' '.$candidate->last_name.'.',
                'priority' => 'normal',
                'deep_link' => '/recruitment/interviews/'.$interview->id,
                'data' => [
                    'interview_id' => $interview->id,
                    'candidate_id' => $candidate->id,
                    'job_requisition_id' => $candidate->job_requisition_id,
                ],
            ], $actor);

            return $this->loadInterview($interview);
        });
    }

    public function cancel(User $actor, int $interviewId, string $comment): Interview
    {
        $interview = $this->accessScopeService->resolveAccessibleInterview($actor, $interviewId);

        if (! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'interview' => ['Cancelling interviews requires recruitment management permission.'],
            ]);
        }

        if ($interview->status !== 'scheduled') {
            throw ValidationException::withMessages([
                'interview' => ['Only scheduled interviews can be cancelled.'],
            ]);
        }

        $interview->forceFill([
            'status' => 'cancelled',
            'cancellation_reason' => $comment,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'recruitment.interview.cancelled',
            actor: $actor,
            metadata: [
                'interview_id' => $interview->id,
                'candidate_id' => $interview->candidate_id,
                'comment' => $comment,
            ],
            entityType: 'interview',
            entityId: (string) $interview->id,
        );

        $candidate = $interview->candidate()->with('recruiter')->first();
        $recruiter = $candidate?->recruiter;

        if ($recruiter && $recruiter->id !== $actor->id) {
            $this->notificationService->sendDirect($recruiter, [
                'type' => 'recruitment',
                'channel' => 'in_app',
                'title' => 'Interview cancelled',
                'message' => 'An interview was cancelled for candidate '.$candidate->first_name.' '.$candidate->last_name.'.',
                'priority' => 'normal',
                'deep_link' => '/recruitment/interviews/'.$interview->id,
                'data' => [
                    'interview_id' => $interview->id,
                    'candidate_id' => $interview->candidate_id,
                    'job_requisition_id' => $interview->job_requisition_id,
                ],
            ], $actor);
        }

        return $this->loadInterview($interview);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function submitFeedback(User $actor, int $interviewId, array $payload): Interview
    {
        $interview = $this->accessScopeService->resolveAccessibleInterview($actor, $interviewId);

        if ($interview->interviewer_user_id !== $actor->id && ! $actor->can('recruitment.manage')) {
            throw ValidationException::withMessages([
                'interview' => ['Only the assigned interviewer or a recruitment manager can submit scorecard feedback.'],
            ]);
        }

        if ($interview->feedback()->exists()) {
            throw ValidationException::withMessages([
                'interview' => ['Interview feedback has already been submitted and remains immutable.'],
            ]);
        }

        if ($interview->status !== 'scheduled') {
            throw ValidationException::withMessages([
                'interview' => ['Feedback can only be submitted for scheduled interviews.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $interview, $payload): Interview {
            InterviewFeedback::query()->create([
                'company_id' => $interview->company_id,
                'interview_id' => $interview->id,
                'candidate_id' => $interview->candidate_id,
                'job_requisition_id' => $interview->job_requisition_id,
                'interviewer_user_id' => $interview->interviewer_user_id,
                'rating' => (int) $payload['rating'],
                'recommendation' => $payload['recommendation'],
                'comments' => trim((string) $payload['comments']),
                'strengths' => $payload['strengths'] ?? null,
                'concerns' => $payload['concerns'] ?? null,
                'submitted_at' => now(),
            ]);

            $interview->forceFill([
                'status' => 'completed',
                'updated_by_user_id' => $actor->id,
            ])->save();

            $candidate = $interview->candidate()->first();
            $recruiter = $candidate?->recruiter;

            $this->auditLogger->record(
                eventType: 'recruitment.interview.feedback_submitted',
                actor: $actor,
                metadata: [
                    'interview_id' => $interview->id,
                    'candidate_id' => $interview->candidate_id,
                    'job_requisition_id' => $interview->job_requisition_id,
                    'rating' => (int) $payload['rating'],
                    'recommendation' => $payload['recommendation'],
                ],
                entityType: 'interview_feedback',
                entityId: (string) $interview->id,
            );

            if ($recruiter && $recruiter->id !== $actor->id) {
                $this->notificationService->sendDirect($recruiter, [
                    'type' => 'recruitment',
                    'channel' => 'in_app',
                    'title' => 'Interview feedback submitted',
                    'message' => 'Interview feedback is ready for candidate '.$candidate->first_name.' '.$candidate->last_name.'.',
                    'priority' => 'normal',
                    'deep_link' => '/recruitment/interviews/'.$interview->id,
                    'data' => [
                        'interview_id' => $interview->id,
                        'candidate_id' => $interview->candidate_id,
                    ],
                ], $actor);
            }

            return $this->loadInterview($interview);
        });
    }

    private function resolveInterviewCode(): string
    {
        $nextOrdinal = ((int) Interview::query()->lockForUpdate()->max('id')) + 1;

        return sprintf('INT-%04d', $nextOrdinal);
    }

    private function assertNoScheduleOverlap(
        int $companyId,
        int $candidateId,
        int $interviewerUserId,
        Carbon $scheduledStartAt,
        Carbon $scheduledEndAt,
    ): void {
        $overlappingInterview = Interview::query()
            ->where('company_id', $companyId)
            ->where('status', 'scheduled')
            ->where('scheduled_start_at', '<', $scheduledEndAt)
            ->where('scheduled_end_at', '>', $scheduledStartAt)
            ->where(function (Builder $builder) use ($candidateId, $interviewerUserId): void {
                $builder->where('candidate_id', $candidateId)
                    ->orWhere('interviewer_user_id', $interviewerUserId);
            })
            ->first();

        if (! $overlappingInterview) {
            return;
        }

        throw ValidationException::withMessages([
            'scheduled_start_at' => ['The selected interview slot overlaps with an existing scheduled interview for the candidate or interviewer.'],
        ]);
    }

    private function moveCandidateToInterviewStage(Candidate $candidate, User $actor): void
    {
        if (in_array($candidate->current_stage, ['interview', 'offer', 'hired'], true)) {
            return;
        }

        $candidate->stageTransitions()->create([
            'company_id' => $candidate->company_id,
            'from_stage' => $candidate->current_stage,
            'to_stage' => 'interview',
            'resulting_status' => 'active',
            'comment' => 'Candidate entered interview coordination.',
            'transitioned_by_user_id' => $actor->id,
            'transitioned_at' => now(),
        ]);

        $candidate->forceFill([
            'current_stage' => 'interview',
            'status' => 'active',
            'stage_entered_at' => now(),
            'updated_by_user_id' => $actor->id,
        ])->save();
    }

    private function loadInterview(Interview $interview): Interview
    {
        return $interview->load([
            'candidate.recruiter',
            'requisition',
            'interviewer',
            'feedback.interviewer',
        ]);
    }
}
