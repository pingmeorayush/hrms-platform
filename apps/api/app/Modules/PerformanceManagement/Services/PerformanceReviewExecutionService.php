<?php

namespace App\Modules\PerformanceManagement\Services;

use App\Models\Employee;
use App\Models\PerformanceCompetency;
use App\Models\PerformanceGoal;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewCycle;
use App\Models\PerformanceReviewSubmission;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type PerformanceReviewFilters array{
 *   status?: string,
 *   review_cycle_id?: int|string,
 *   employee_id?: int|string,
 *   per_page?: int|string
 * }
 * @phpstan-type VisibilityRulesPayload array{
 *   employee_can_view_manager_assessment_before_publish?: bool|int|string,
 *   employee_can_view_peer_feedback_after_publish?: bool|int|string,
 *   peer_feedback_anonymous_to_employee?: bool|int|string,
 *   manager_can_view_peer_feedback?: bool|int|string,
 *   reviewer_can_view_other_reviewer_feedback?: bool|int|string
 * }
 * @phpstan-type VisibilityRules array{
 *   employee_can_view_manager_assessment_before_publish: bool,
 *   employee_can_view_peer_feedback_after_publish: bool,
 *   peer_feedback_anonymous_to_employee: bool,
 *   manager_can_view_peer_feedback: bool,
 *   reviewer_can_view_other_reviewer_feedback: bool
 * }
 * @phpstan-type GoalSnapshotItem array{
 *   id: int,
 *   goal_code: string,
 *   title: string,
 *   description: string|null,
 *   due_on: string|null,
 *   weight_percent: float,
 *   success_metric: array<string, mixed>|null,
 *   status: string
 * }
 * @phpstan-type CompetencySnapshotItem array{
 *   id: int,
 *   code: string,
 *   name: string,
 *   category: string,
 *   scale_definition: array<string, mixed>|null
 * }
 * @phpstan-type ReviewCreatePayload array{
 *   performance_review_cycle_id: int|string,
 *   employee_id: int|string,
 *   reviewer_user_ids?: list<mixed>,
 *   visibility_rules?: VisibilityRulesPayload,
 *   launch_immediately?: bool|int|string
 * }
 * @phpstan-type ReviewUpdatePayload array{
 *   reviewer_user_ids?: list<mixed>,
 *   visibility_rules?: VisibilityRulesPayload
 * }
 * @phpstan-type ReviewSectionSubmissionPayload array{
 *   key: string,
 *   rating: int|float|string,
 *   comment?: string|null
 * }
 * @phpstan-type ReviewSectionSubmission array{
 *   key: string,
 *   rating: float,
 *   comment: string|null
 * }
 * @phpstan-type ReviewCompetencySubmissionPayload array{
 *   competency_id: int|string,
 *   rating: int|float|string,
 *   comment?: string|null
 * }
 * @phpstan-type ReviewCompetencySubmission array{
 *   competency_id: int,
 *   rating: float,
 *   comment: string|null
 * }
 * @phpstan-type ReviewSubmissionPayload array{
 *   sections: list<ReviewSectionSubmissionPayload>,
 *   competencies?: list<ReviewCompetencySubmissionPayload>,
 *   overall_rating: int|float|string,
 *   summary: string,
 *   confidential_notes?: string|null
 * }
 * @phpstan-type ReviewSubmissionData array{
 *   sections: list<ReviewSectionSubmission>,
 *   competencies: list<ReviewCompetencySubmission>,
 *   overall_rating: float,
 *   summary: string,
 *   confidential_notes: string|null
 * }
 * @phpstan-type ReviewSectionAdjustmentPayload array{
 *   key: string,
 *   calibrated_rating: int|float|string,
 *   note?: string|null
 * }
 * @phpstan-type ReviewSectionAdjustment array{
 *   key: string,
 *   calibrated_rating: float,
 *   note: string|null
 * }
 * @phpstan-type ReviewCompetencyAdjustmentPayload array{
 *   competency_id: int|string,
 *   calibrated_rating: int|float|string,
 *   note?: string|null
 * }
 * @phpstan-type ReviewCompetencyAdjustment array{
 *   competency_id: int,
 *   calibrated_rating: float,
 *   note: string|null
 * }
 * @phpstan-type ReviewCalibrationPayload array{
 *   overall_rating: int|float|string,
 *   summary: string,
 *   confidential_notes?: string|null,
 *   section_adjustments?: list<ReviewSectionAdjustmentPayload>,
 *   competency_adjustments?: list<ReviewCompetencyAdjustmentPayload>
 * }
 * @phpstan-type ReviewCalibrationData array{
 *   overall_rating: float,
 *   summary: string,
 *   confidential_notes: string|null,
 *   section_adjustments: list<ReviewSectionAdjustment>,
 *   competency_adjustments: list<ReviewCompetencyAdjustment>
 * }
 * @phpstan-type ReviewFinalPayload array{
 *   final_rating: int|float|string,
 *   summary: string,
 *   employee_visible_summary: string,
 *   recommendation?: string|null
 * }
 * @phpstan-type ReviewFinalData array{
 *   final_rating: float,
 *   summary: string,
 *   employee_visible_summary: string,
 *   recommendation: string|null,
 *   finalized_by_user_id: int,
 *   finalized_by_name: string
 * }
 * @phpstan-type RatingScale array{min: int, max: int}
 */
class PerformanceReviewExecutionService
{
    public function __construct(
        private readonly PerformanceAccessScopeService $accessScopeService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  PerformanceReviewFilters  $filters
     * @return LengthAwarePaginator<int, PerformanceReview>
     */
    public function searchReviews(User $actor, array $filters): LengthAwarePaginator
    {
        return $this->accessScopeService
            ->reviewsQuery($actor)
            ->when(array_key_exists('status', $filters), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(array_key_exists('review_cycle_id', $filters), fn (Builder $builder) => $builder->where('performance_review_cycle_id', $filters['review_cycle_id']))
            ->when(array_key_exists('employee_id', $filters), fn (Builder $builder) => $builder->where('employee_id', $filters['employee_id']))
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findForView(User $actor, int $reviewId): PerformanceReview
    {
        return $this->accessScopeService->resolveAccessibleReview($actor, $reviewId);
    }

    /**
     * @param  ReviewCreatePayload  $payload
     */
    public function createReview(User $actor, array $payload): PerformanceReview
    {
        return DB::transaction(function () use ($actor, $payload): PerformanceReview {
            $this->ensureCanManageReview($actor);
            $cycle = $this->accessScopeService->resolveAccessibleReviewCycle($actor, (int) $payload['performance_review_cycle_id']);
            $employee = $this->resolveEmployeeForCompany($actor, (int) $payload['employee_id']);

            if (! in_array($cycle->status, ['scheduled', 'active'], true)) {
                throw ValidationException::withMessages([
                    'performance_review_cycle_id' => ['Reviews can only be created from scheduled or active review cycles.'],
                ]);
            }

            $this->ensureEmployeeMatchesCyclePopulation($employee, $cycle);

            $managerEmployee = $employee->manager()->with('user')->first();
            $participantRules = $cycle->participant_rules['reviewers'] ?? [];

            if (($participantRules['manager_review_required'] ?? false) && ! $managerEmployee?->user_id) {
                throw ValidationException::withMessages([
                    'employee_id' => ['A manager-linked user is required before this review cycle can launch manager review.'],
                ]);
            }

            $reviewerUserIds = $this->normalizeReviewerUserIds($actor, $payload['reviewer_user_ids'] ?? [], $employee, $managerEmployee);
            $visibilityRules = $this->mergeVisibilityRules($payload['visibility_rules'] ?? []);
            $goalSnapshot = $this->buildGoalSnapshot($actor, $employee->id, $cycle->id);
            $competencySnapshot = $this->buildCompetencySnapshot($cycle);
            $launchImmediately = (bool) ($payload['launch_immediately'] ?? true);
            $status = $launchImmediately
                ? $this->initialStatusForReview($cycle, $reviewerUserIds)
                : 'draft';

            $review = PerformanceReview::query()->create([
                'company_id' => $actor->company_id,
                'performance_review_cycle_id' => $cycle->id,
                'employee_id' => $employee->id,
                'manager_employee_id' => $managerEmployee?->id,
                'reviewer_user_ids' => $reviewerUserIds,
                'goal_snapshot' => $goalSnapshot,
                'competency_snapshot' => $competencySnapshot,
                'visibility_rules' => $visibilityRules,
                'status' => $status,
                'launched_at' => $launchImmediately ? now() : null,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
            ]);

            $this->auditLogger->record(
                eventType: 'performance.review.created',
                actor: $actor,
                metadata: [
                    'performance_review_id' => $review->id,
                    'performance_review_cycle_id' => $cycle->id,
                    'employee_id' => $employee->id,
                    'manager_employee_id' => $managerEmployee?->id,
                    'reviewer_user_ids' => $reviewerUserIds,
                    'status' => $review->status,
                ],
                entityType: 'performance_review',
                entityId: (string) $review->id,
            );

            return $this->accessScopeService->resolveAccessibleReview($actor, $review->id);
        });
    }

    /**
     * @param  ReviewUpdatePayload  $payload
     */
    public function updateReview(User $actor, PerformanceReview $review, array $payload): PerformanceReview
    {
        return DB::transaction(function () use ($actor, $review, $payload): PerformanceReview {
            $this->ensureCanManageReview($actor);
            if (! in_array($review->status, ['draft', 'self_assessment', 'manager_review', 'reopened'], true)) {
                throw ValidationException::withMessages([
                    'review' => ['Reviewer assignments and visibility rules can only be updated before finalization.'],
                ]);
            }

            $before = $review->only(['reviewer_user_ids', 'visibility_rules', 'status']);

            $employee = $review->employee()->with('manager.user')->firstOrFail();
            $managerEmployee = $employee->manager;

            $reviewerUserIds = array_key_exists('reviewer_user_ids', $payload)
                ? $this->normalizeReviewerUserIds($actor, $payload['reviewer_user_ids'], $employee, $managerEmployee)
                : ($review->reviewer_user_ids ?? []);
            $visibilityRules = array_key_exists('visibility_rules', $payload)
                ? $this->mergeVisibilityRules($payload['visibility_rules'])
                : ($review->visibility_rules ?? $this->mergeVisibilityRules([]));

            $review->forceFill([
                'reviewer_user_ids' => $reviewerUserIds,
                'visibility_rules' => $visibilityRules,
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'performance.review.updated',
                actor: $actor,
                metadata: [
                    'before' => $before,
                    'after' => $review->only(['reviewer_user_ids', 'visibility_rules', 'status']),
                ],
                entityType: 'performance_review',
                entityId: (string) $review->id,
            );

            return $this->accessScopeService->resolveAccessibleReview($actor, $review->id);
        });
    }

    /**
     * @param  ReviewSubmissionPayload  $payload
     */
    public function submitReview(User $actor, PerformanceReview $review, array $payload): PerformanceReview
    {
        return DB::transaction(function () use ($actor, $review, $payload): PerformanceReview {
            $review->loadMissing(['reviewCycle', 'submissions', 'employee.user', 'managerEmployee.user']);
            $actorRole = $this->accessScopeService->determineActorRole($actor, $review);

            if (! in_array($actorRole, ['self', 'manager', 'reviewer'], true)) {
                throw ValidationException::withMessages([
                    'review' => ['This session is not configured as a self, manager, or reviewer participant for the selected review.'],
                ]);
            }

            $this->ensureSubmissionWindowIsOpen($review, $actorRole);
            $normalizedPayload = $this->normalizeSubmissionPayload($review, $payload);
            $visibilityScope = $this->visibilityScopeForRole($review, $actorRole);

            $submission = PerformanceReviewSubmission::query()->firstOrNew([
                'performance_review_id' => $review->id,
                'reviewer_user_id' => $actor->id,
                'role_type' => $actorRole,
            ]);

            $submission->fill([
                'company_id' => $actor->company_id,
                'reviewer_employee_id' => $actor->employee?->id,
                'visibility_scope' => $visibilityScope,
                'section_payload' => $normalizedPayload['sections'],
                'competency_payload' => $normalizedPayload['competencies'],
                'overall_rating' => $normalizedPayload['overall_rating'],
                'summary' => $normalizedPayload['summary'],
                'confidential_notes' => $normalizedPayload['confidential_notes'],
                'submitted_at' => now(),
            ]);
            $submission->save();

            $this->markSubmissionTimestamp($review, $actorRole, $actor);
            $this->recomputeReviewStatus($review, $actor);

            $this->auditLogger->record(
                eventType: 'performance.review.submission.submitted',
                actor: $actor,
                metadata: [
                    'performance_review_id' => $review->id,
                    'role_type' => $actorRole,
                    'overall_rating' => $submission->overall_rating,
                    'status' => $review->status,
                ],
                entityType: 'performance_review_submission',
                entityId: (string) $submission->id,
            );

            return $this->accessScopeService->resolveAccessibleReview($actor, $review->id);
        });
    }

    /**
     * @param  ReviewCalibrationPayload  $payload
     */
    public function calibrateReview(User $actor, PerformanceReview $review, array $payload): PerformanceReview
    {
        return DB::transaction(function () use ($actor, $review, $payload): PerformanceReview {
            $review->loadMissing(['reviewCycle', 'submissions']);
            $this->ensureCanAdministerReview($actor);
            $this->ensureReviewInputsComplete($review);

            $normalized = $this->normalizeCalibrationPayload($review, $payload);

            $review->forceFill([
                'calibration_payload' => $normalized,
                'calibration_completed_at' => now(),
                'status' => 'calibration',
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'performance.review.calibrated',
                actor: $actor,
                metadata: [
                    'performance_review_id' => $review->id,
                    'overall_rating' => $normalized['overall_rating'],
                ],
                entityType: 'performance_review',
                entityId: (string) $review->id,
            );

            return $this->accessScopeService->resolveAccessibleReview($actor, $review->id);
        });
    }

    /**
     * @param  ReviewFinalPayload  $payload
     */
    public function finalizeReview(User $actor, PerformanceReview $review, array $payload): PerformanceReview
    {
        return DB::transaction(function () use ($actor, $review, $payload): PerformanceReview {
            $review->loadMissing(['reviewCycle', 'submissions']);
            $this->ensureCanAdministerReview($actor);
            $this->ensureReviewInputsComplete($review);

            $normalized = $this->normalizeFinalPayload($review, $payload, $actor);

            $review->forceFill([
                'final_payload' => $normalized,
                'finalized_at' => now(),
                'status' => 'finalized',
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'performance.review.finalized',
                actor: $actor,
                metadata: [
                    'performance_review_id' => $review->id,
                    'final_rating' => $normalized['final_rating'],
                ],
                entityType: 'performance_review',
                entityId: (string) $review->id,
            );

            return $this->accessScopeService->resolveAccessibleReview($actor, $review->id);
        });
    }

    public function publishReview(User $actor, PerformanceReview $review): PerformanceReview
    {
        return DB::transaction(function () use ($actor, $review): PerformanceReview {
            $this->ensureCanManageReview($actor);

            if ($review->status !== 'finalized') {
                throw ValidationException::withMessages([
                    'review' => ['Only finalized reviews can be published.'],
                ]);
            }

            $review->forceFill([
                'status' => 'published',
                'published_at' => now(),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'performance.review.published',
                actor: $actor,
                metadata: [
                    'performance_review_id' => $review->id,
                ],
                entityType: 'performance_review',
                entityId: (string) $review->id,
            );

            return $this->accessScopeService->resolveAccessibleReview($actor, $review->id);
        });
    }

    public function reopenReview(User $actor, PerformanceReview $review, string $reason): PerformanceReview
    {
        return DB::transaction(function () use ($actor, $review, $reason): PerformanceReview {
            $this->ensureCanAdministerReview($actor);

            if (! in_array($review->status, ['finalized', 'published'], true)) {
                throw ValidationException::withMessages([
                    'review' => ['Only finalized or published reviews can be reopened.'],
                ]);
            }

            $review->forceFill([
                'status' => 'reopened',
                'reopened_at' => now(),
                'reopen_count' => $review->reopen_count + 1,
                'reopened_reason' => trim($reason),
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'performance.review.reopened',
                actor: $actor,
                metadata: [
                    'performance_review_id' => $review->id,
                    'reason' => trim($reason),
                    'reopen_count' => $review->reopen_count,
                ],
                entityType: 'performance_review',
                entityId: (string) $review->id,
            );

            return $this->accessScopeService->resolveAccessibleReview($actor, $review->id);
        });
    }

    /**
     * @param  array<int, mixed>  $reviewerUserIds
     * @return array<int, int>
     */
    private function normalizeReviewerUserIds(User $actor, array $reviewerUserIds, Employee $employee, ?Employee $managerEmployee): array
    {
        $normalized = collect($reviewerUserIds)
            ->map(fn (mixed $value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->values()
            ->all();

        if ($normalized === []) {
            return [];
        }

        $invalidIds = array_filter($normalized, function (int $userId) use ($actor, $employee, $managerEmployee): bool {
            if ($employee->user_id === $userId) {
                return true;
            }

            if ($managerEmployee?->user_id === $userId) {
                return true;
            }

            return ! User::query()
                ->where('company_id', $actor->company_id)
                ->whereKey($userId)
                ->exists();
        });

        if ($invalidIds !== []) {
            throw ValidationException::withMessages([
                'reviewer_user_ids' => ['Assigned reviewers must belong to the active tenant and cannot duplicate the subject employee or direct manager.'],
            ]);
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  VisibilityRulesPayload|array<string, mixed>  $payload
     * @return VisibilityRules
     */
    private function mergeVisibilityRules(array $payload): array
    {
        return [
            'employee_can_view_manager_assessment_before_publish' => (bool) ($payload['employee_can_view_manager_assessment_before_publish'] ?? false),
            'employee_can_view_peer_feedback_after_publish' => (bool) ($payload['employee_can_view_peer_feedback_after_publish'] ?? false),
            'peer_feedback_anonymous_to_employee' => (bool) ($payload['peer_feedback_anonymous_to_employee'] ?? true),
            'manager_can_view_peer_feedback' => (bool) ($payload['manager_can_view_peer_feedback'] ?? true),
            'reviewer_can_view_other_reviewer_feedback' => (bool) ($payload['reviewer_can_view_other_reviewer_feedback'] ?? false),
        ];
    }

    /**
     * @return list<GoalSnapshotItem>
     */
    private function buildGoalSnapshot(User $actor, int $employeeId, int $reviewCycleId): array
    {
        return PerformanceGoal::query()
            ->where('company_id', $actor->company_id)
            ->where('owner_employee_id', $employeeId)
            ->where('performance_review_cycle_id', $reviewCycleId)
            ->where('status', '!=', 'archived')
            ->orderBy('due_on')
            ->orderBy('id')
            ->get()
            ->map(fn (PerformanceGoal $goal): array => [
                'id' => $goal->id,
                'goal_code' => $goal->goal_code,
                'title' => $goal->title,
                'description' => $goal->description,
                'due_on' => $goal->due_on?->toDateString(),
                'weight_percent' => $goal->weight_percent,
                'success_metric' => $goal->success_metric,
                'status' => $goal->status,
            ])
            ->all();
    }

    /**
     * @return list<CompetencySnapshotItem>
     */
    private function buildCompetencySnapshot(PerformanceReviewCycle $cycle): array
    {
        $competencyIds = $this->normalizeIntegerList($cycle->competency_visibility['required_competency_ids'] ?? []);

        if ($competencyIds === []) {
            return [];
        }

        return PerformanceCompetency::query()
            ->whereIn('id', $competencyIds)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (PerformanceCompetency $competency): array => [
                'id' => $competency->id,
                'code' => $competency->code,
                'name' => $competency->name,
                'category' => $competency->category,
                'scale_definition' => $competency->scale_definition,
            ])
            ->all();
    }

    /**
     * @param  list<int>  $reviewerUserIds
     */
    private function initialStatusForReview(PerformanceReviewCycle $cycle, array $reviewerUserIds): string
    {
        $reviewers = is_array($cycle->participant_rules['reviewers'] ?? null)
            ? $cycle->participant_rules['reviewers']
            : [];

        if (($reviewers['self_review_required'] ?? false) === true) {
            return 'self_assessment';
        }

        if (($reviewers['manager_review_required'] ?? false) === true || $reviewerUserIds !== []) {
            return 'manager_review';
        }

        return 'calibration';
    }

    private function ensureEmployeeMatchesCyclePopulation(Employee $employee, PerformanceReviewCycle $cycle): void
    {
        $population = is_array($cycle->participant_rules['population'] ?? null)
            ? $cycle->participant_rules['population']
            : [];

        $employmentStatuses = $this->normalizeStringList($population['employment_statuses'] ?? []);
        if ($employmentStatuses !== [] && ! in_array($employee->employment_status, $employmentStatuses, true)) {
            throw ValidationException::withMessages([
                'employee_id' => ['The employee does not match the configured review-cycle employment-status population.'],
            ]);
        }

        $employmentTypes = $this->normalizeStringList($population['employment_types'] ?? []);
        if ($employmentTypes !== [] && ! in_array($employee->employment_type, $employmentTypes, true)) {
            throw ValidationException::withMessages([
                'employee_id' => ['The employee does not match the configured review-cycle employment-type population.'],
            ]);
        }

        $departmentIds = $this->normalizeIntegerList($population['department_ids'] ?? []);
        if ($departmentIds !== [] && ! in_array((int) $employee->department_id, $departmentIds, true)) {
            throw ValidationException::withMessages([
                'employee_id' => ['The employee does not match the configured review-cycle department population.'],
            ]);
        }

        $designationIds = $this->normalizeIntegerList($population['designation_ids'] ?? []);
        if ($designationIds !== [] && ! in_array((int) $employee->designation_id, $designationIds, true)) {
            throw ValidationException::withMessages([
                'employee_id' => ['The employee does not match the configured review-cycle designation population.'],
            ]);
        }
    }

    private function resolveEmployeeForCompany(User $actor, int $employeeId): Employee
    {
        $employee = Employee::query()->with('manager.user')->findOrFail($employeeId);
        $this->accessScopeService->ensureCompanyMatch(Employee::class, $employee->company_id === $actor->company_id);

        return $employee;
    }

    /**
     * @param  ReviewSubmissionPayload  $payload
     * @return ReviewSubmissionData
     */
    private function normalizeSubmissionPayload(PerformanceReview $review, array $payload): array
    {
        $ratingScale = $this->resolveRatingScale($review->reviewCycle->review_template['rating_scale'] ?? null);
        $templateSections = $this->normalizeArrayRows($review->reviewCycle->review_template['sections'] ?? []);
        $sectionKeys = array_map(
            static fn (array $section): string => trim((string) ($section['key'] ?? '')),
            $templateSections,
        );
        $requiredSectionKeys = array_map(
            static fn (array $section): string => trim((string) ($section['key'] ?? '')),
            array_values(array_filter(
                $templateSections,
                static fn (array $section): bool => (bool) ($section['required'] ?? false),
            )),
        );

        $sectionsByKey = [];
        foreach ($payload['sections'] as $section) {
            $key = trim((string) $section['key']);
            if (! in_array($key, $sectionKeys, true)) {
                throw ValidationException::withMessages([
                    'sections' => ["The section key [{$key}] is not part of the configured review template."],
                ]);
            }

            $rating = round((float) $section['rating'], 2);
            $this->ensureRatingWithinScale($rating, $ratingScale['min'], $ratingScale['max'], 'sections');

            $sectionsByKey[$key] = [
                'key' => $key,
                'rating' => $rating,
                'comment' => filled($section['comment'] ?? null) ? trim((string) $section['comment']) : null,
            ];
        }

        foreach ($requiredSectionKeys as $requiredSectionKey) {
            if (! array_key_exists($requiredSectionKey, $sectionsByKey)) {
                throw ValidationException::withMessages([
                    'sections' => ["The required review section [{$requiredSectionKey}] must be submitted."],
                ]);
            }
        }

        $competencySnapshotIds = $this->normalizeIntegerList(
            array_column($this->normalizeArrayRows($review->competency_snapshot), 'id'),
        );

        $competencies = array_map(function (array $competency) use ($competencySnapshotIds, $ratingScale): array {
            $competencyId = (int) $competency['competency_id'];
            if (! in_array($competencyId, $competencySnapshotIds, true)) {
                throw ValidationException::withMessages([
                    'competencies' => ['One or more submitted competencies are not part of the review snapshot.'],
                ]);
            }

            $rating = round((float) $competency['rating'], 2);
            $this->ensureRatingWithinScale($rating, $ratingScale['min'], $ratingScale['max'], 'competencies');

            return [
                'competency_id' => $competencyId,
                'rating' => $rating,
                'comment' => filled($competency['comment'] ?? null) ? trim((string) $competency['comment']) : null,
            ];
        }, $payload['competencies'] ?? []);

        $overallRating = round((float) $payload['overall_rating'], 2);
        $this->ensureRatingWithinScale($overallRating, $ratingScale['min'], $ratingScale['max'], 'overall_rating');

        return [
            'sections' => array_values($sectionsByKey),
            'competencies' => $competencies,
            'overall_rating' => $overallRating,
            'summary' => trim((string) $payload['summary']),
            'confidential_notes' => filled($payload['confidential_notes'] ?? null)
                ? trim((string) $payload['confidential_notes'])
                : null,
        ];
    }

    /**
     * @param  ReviewCalibrationPayload  $payload
     * @return ReviewCalibrationData
     */
    private function normalizeCalibrationPayload(PerformanceReview $review, array $payload): array
    {
        $ratingScale = $this->resolveRatingScale($review->reviewCycle->review_template['rating_scale'] ?? null);
        $overallRating = round((float) $payload['overall_rating'], 2);
        $this->ensureRatingWithinScale($overallRating, $ratingScale['min'], $ratingScale['max'], 'overall_rating');

        $sectionKeys = array_map(
            static fn (array $section): string => trim((string) ($section['key'] ?? '')),
            $this->normalizeArrayRows($review->reviewCycle->review_template['sections'] ?? []),
        );
        $competencySnapshotIds = $this->normalizeIntegerList(
            array_column($this->normalizeArrayRows($review->competency_snapshot), 'id'),
        );

        $sectionAdjustments = array_map(function (array $adjustment) use ($sectionKeys, $ratingScale): array {
            $key = trim((string) $adjustment['key']);
            if (! in_array($key, $sectionKeys, true)) {
                throw ValidationException::withMessages([
                    'section_adjustments' => ["The section key [{$key}] is not part of the configured review template."],
                ]);
            }

            $rating = round((float) $adjustment['calibrated_rating'], 2);
            $this->ensureRatingWithinScale($rating, $ratingScale['min'], $ratingScale['max'], 'section_adjustments');

            return [
                'key' => $key,
                'calibrated_rating' => $rating,
                'note' => filled($adjustment['note'] ?? null) ? trim((string) $adjustment['note']) : null,
            ];
        }, $payload['section_adjustments'] ?? []);

        $competencyAdjustments = array_map(function (array $adjustment) use ($competencySnapshotIds, $ratingScale): array {
            $competencyId = (int) $adjustment['competency_id'];
            if (! in_array($competencyId, $competencySnapshotIds, true)) {
                throw ValidationException::withMessages([
                    'competency_adjustments' => ['One or more calibration competencies are not part of the review snapshot.'],
                ]);
            }

            $rating = round((float) $adjustment['calibrated_rating'], 2);
            $this->ensureRatingWithinScale($rating, $ratingScale['min'], $ratingScale['max'], 'competency_adjustments');

            return [
                'competency_id' => $competencyId,
                'calibrated_rating' => $rating,
                'note' => filled($adjustment['note'] ?? null) ? trim((string) $adjustment['note']) : null,
            ];
        }, $payload['competency_adjustments'] ?? []);

        return [
            'overall_rating' => $overallRating,
            'summary' => trim((string) $payload['summary']),
            'confidential_notes' => filled($payload['confidential_notes'] ?? null)
                ? trim((string) $payload['confidential_notes'])
                : null,
            'section_adjustments' => $sectionAdjustments,
            'competency_adjustments' => $competencyAdjustments,
        ];
    }

    /**
     * @param  ReviewFinalPayload  $payload
     * @return ReviewFinalData
     */
    private function normalizeFinalPayload(PerformanceReview $review, array $payload, User $actor): array
    {
        $ratingScale = $this->resolveRatingScale($review->reviewCycle->review_template['rating_scale'] ?? null);
        $finalRating = round((float) $payload['final_rating'], 2);
        $this->ensureRatingWithinScale($finalRating, $ratingScale['min'], $ratingScale['max'], 'final_rating');

        return [
            'final_rating' => $finalRating,
            'summary' => trim((string) $payload['summary']),
            'employee_visible_summary' => trim((string) $payload['employee_visible_summary']),
            'recommendation' => filled($payload['recommendation'] ?? null) ? trim((string) $payload['recommendation']) : null,
            'finalized_by_user_id' => $actor->id,
            'finalized_by_name' => $actor->name,
        ];
    }

    private function ensureSubmissionWindowIsOpen(PerformanceReview $review, string $actorRole): void
    {
        $reviewerRules = $review->reviewCycle->participant_rules['reviewers'] ?? [];

        if ($actorRole === 'self') {
            if (! ($reviewerRules['self_review_required'] ?? false)) {
                throw ValidationException::withMessages([
                    'review' => ['Self review is not enabled for this review cycle.'],
                ]);
            }

            if (! in_array($review->status, ['self_assessment', 'reopened'], true)) {
                throw ValidationException::withMessages([
                    'review' => ['Self review content can only be submitted during self-assessment or reopened states.'],
                ]);
            }

            if ($review->reviewCycle->self_review_due_on && now()->toDateString() > $review->reviewCycle->self_review_due_on->toDateString()) {
                throw ValidationException::withMessages([
                    'review' => ['The self-review submission deadline has passed for this cycle.'],
                ]);
            }

            return;
        }

        if (! in_array($review->status, ['manager_review', 'reopened'], true)) {
            throw ValidationException::withMessages([
                'review' => ['Manager and reviewer input can only be submitted during manager-review or reopened states.'],
            ]);
        }

        if ($review->reviewCycle->manager_review_due_on && now()->toDateString() > $review->reviewCycle->manager_review_due_on->toDateString()) {
            throw ValidationException::withMessages([
                'review' => ['The manager-review submission deadline has passed for this cycle.'],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function visibilityScopeForRole(PerformanceReview $review, string $actorRole): array
    {
        $rules = $review->visibility_rules ?? [];

        return match ($actorRole) {
            'self' => [
                'visible_to_self' => true,
                'visible_to_manager' => true,
                'visible_to_hr' => true,
                'visible_to_reviewers' => false,
                'anonymous_to_employee' => false,
            ],
            'manager' => [
                'visible_to_self' => (bool) ($rules['employee_can_view_manager_assessment_before_publish'] ?? false),
                'visible_to_manager' => true,
                'visible_to_hr' => true,
                'visible_to_reviewers' => false,
                'anonymous_to_employee' => false,
            ],
            default => [
                'visible_to_self' => (bool) ($rules['employee_can_view_peer_feedback_after_publish'] ?? false),
                'visible_to_manager' => (bool) ($rules['manager_can_view_peer_feedback'] ?? true),
                'visible_to_hr' => true,
                'visible_to_reviewers' => (bool) ($rules['reviewer_can_view_other_reviewer_feedback'] ?? false),
                'anonymous_to_employee' => (bool) ($rules['peer_feedback_anonymous_to_employee'] ?? true),
            ],
        };
    }

    private function markSubmissionTimestamp(PerformanceReview $review, string $actorRole, User $actor): void
    {
        $updates = [
            'updated_by_user_id' => $actor->id,
        ];

        if ($actorRole === 'self') {
            $updates['self_submitted_at'] = now();
        }

        if ($actorRole === 'manager') {
            $updates['manager_submitted_at'] = now();
        }

        $review->forceFill($updates)->save();
    }

    private function recomputeReviewStatus(PerformanceReview $review, User $actor): void
    {
        $review->load('submissions');
        $reviewerRules = $review->reviewCycle->participant_rules['reviewers'] ?? [];
        $selfRequired = (bool) ($reviewerRules['self_review_required'] ?? false);
        $managerRequired = (bool) ($reviewerRules['manager_review_required'] ?? false);
        $reviewerUserIds = $review->reviewer_user_ids ?? [];

        $hasSelfSubmission = $review->submissions->contains(fn (PerformanceReviewSubmission $submission): bool => $submission->role_type === 'self');
        $hasManagerSubmission = $review->submissions->contains(fn (PerformanceReviewSubmission $submission): bool => $submission->role_type === 'manager');
        $reviewerSubmissionCount = $review->submissions
            ->filter(fn (PerformanceReviewSubmission $submission): bool => $submission->role_type === 'reviewer' && in_array($submission->reviewer_user_id, $reviewerUserIds, true))
            ->pluck('reviewer_user_id')
            ->unique()
            ->count();

        $status = 'calibration';

        if ($selfRequired && ! $hasSelfSubmission) {
            $status = 'self_assessment';
        } elseif (($managerRequired && ! $hasManagerSubmission) || $reviewerSubmissionCount < count($reviewerUserIds)) {
            $status = 'manager_review';
        }

        $review->forceFill([
            'status' => $status,
            'updated_by_user_id' => $actor->id,
        ])->save();
    }

    private function ensureReviewInputsComplete(PerformanceReview $review): void
    {
        $review->load('submissions');
        $reviewerRules = $review->reviewCycle->participant_rules['reviewers'] ?? [];
        $selfRequired = (bool) ($reviewerRules['self_review_required'] ?? false);
        $managerRequired = (bool) ($reviewerRules['manager_review_required'] ?? false);
        $reviewerUserIds = $review->reviewer_user_ids ?? [];

        $hasSelfSubmission = $review->submissions->contains(fn (PerformanceReviewSubmission $submission): bool => $submission->role_type === 'self');
        $hasManagerSubmission = $review->submissions->contains(fn (PerformanceReviewSubmission $submission): bool => $submission->role_type === 'manager');
        $reviewerSubmissionCount = $review->submissions
            ->filter(fn (PerformanceReviewSubmission $submission): bool => $submission->role_type === 'reviewer' && in_array($submission->reviewer_user_id, $reviewerUserIds, true))
            ->pluck('reviewer_user_id')
            ->unique()
            ->count();

        if (($selfRequired && ! $hasSelfSubmission)
            || ($managerRequired && ! $hasManagerSubmission)
            || $reviewerSubmissionCount < count($reviewerUserIds)) {
            throw ValidationException::withMessages([
                'review' => ['All required self, manager, and configured reviewer submissions must be completed before calibration or finalization.'],
            ]);
        }
    }

    private function ensureCanAdministerReview(User $actor): void
    {
        if (! $actor->can('performance.manage') && ! $actor->can('performance.calibrate')) {
            throw ValidationException::withMessages([
                'review' => ['This action requires performance administration or calibration access.'],
            ]);
        }
    }

    private function ensureCanManageReview(User $actor): void
    {
        if (! $actor->can('performance.manage')) {
            throw ValidationException::withMessages([
                'review' => ['This action requires performance management permission.'],
            ]);
        }
    }

    private function ensureRatingWithinScale(float $rating, int $min, int $max, string $field): void
    {
        if ($rating < $min || $rating > $max) {
            throw ValidationException::withMessages([
                $field => ["Ratings must stay within the configured review scale of {$min} to {$max}."],
            ]);
        }
    }

    /**
     * @return RatingScale
     */
    private function resolveRatingScale(mixed $payload): array
    {
        if (! is_array($payload)) {
            return ['min' => 1, 'max' => 5];
        }

        return [
            'min' => (int) ($payload['min'] ?? 1),
            'max' => (int) ($payload['max'] ?? 5),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeArrayRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $normalized[] = $row;
            }
        }

        return $normalized;
    }

    /**
     * @return list<int>
     */
    private function normalizeIntegerList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            $integer = (int) $value;
            if ($integer > 0) {
                $normalized[] = $integer;
            }
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }

    /**
     * @return list<string>
     */
    private function normalizeStringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            $string = trim((string) $value);
            if ($string !== '') {
                $normalized[] = $string;
            }
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }
}
