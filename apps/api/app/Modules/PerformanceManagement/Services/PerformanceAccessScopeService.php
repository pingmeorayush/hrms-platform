<?php

namespace App\Modules\PerformanceManagement\Services;

use App\Models\PerformanceCompetency;
use App\Models\PerformanceGoal;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewCycle;
use App\Models\PerformanceReviewSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PerformanceAccessScopeService
{
    /**
     * @return Builder<PerformanceGoal>
     */
    public function goalsQuery(User $actor): Builder
    {
        return PerformanceGoal::query()
            ->where('company_id', $actor->company_id)
            ->with(['reviewCycle', 'ownerEmployee', 'department']);
    }

    /**
     * @return Builder<PerformanceCompetency>
     */
    public function competenciesQuery(User $actor): Builder
    {
        return PerformanceCompetency::query()
            ->where('company_id', $actor->company_id);
    }

    /**
     * @return Builder<PerformanceReviewCycle>
     */
    public function reviewCyclesQuery(User $actor): Builder
    {
        return PerformanceReviewCycle::query()
            ->where('company_id', $actor->company_id)
            ->withCount('goals');
    }

    /**
     * @return Builder<PerformanceReview>
     */
    public function reviewsQuery(User $actor): Builder
    {
        $query = PerformanceReview::query()
            ->where('company_id', $actor->company_id)
            ->with([
                'reviewCycle',
                'employee.user',
                'managerEmployee.user',
                'submissions.reviewer',
                'submissions.reviewerEmployee',
            ]);

        if ($actor->can('performance.manage') || $actor->can('performance.calibrate')) {
            return $query;
        }

        $actorEmployeeId = $actor->employee?->id;

        return $query->where(function (Builder $builder) use ($actor, $actorEmployeeId): void {
            if ($actorEmployeeId !== null) {
                $builder
                    ->orWhere('employee_id', $actorEmployeeId)
                    ->orWhere('manager_employee_id', $actorEmployeeId);
            }

            $builder->orWhereJsonContains('reviewer_user_ids', $actor->id);
        });
    }

    public function resolveAccessibleGoal(User $actor, int $goalId): PerformanceGoal
    {
        return $this->goalsQuery($actor)->findOrFail($goalId);
    }

    public function resolveAccessibleCompetency(User $actor, int $competencyId): PerformanceCompetency
    {
        return $this->competenciesQuery($actor)->findOrFail($competencyId);
    }

    public function resolveAccessibleReviewCycle(User $actor, int $reviewCycleId): PerformanceReviewCycle
    {
        return $this->reviewCyclesQuery($actor)->findOrFail($reviewCycleId);
    }

    public function resolveAccessibleReview(User $actor, int $reviewId): PerformanceReview
    {
        return $this->reviewsQuery($actor)->findOrFail($reviewId);
    }

    public function determineActorRole(User $actor, PerformanceReview $review): ?string
    {
        if ($actor->can('performance.manage') || $actor->can('performance.calibrate')) {
            return 'hr';
        }

        $actorEmployeeId = $actor->employee?->id;

        if ($actorEmployeeId !== null && $review->employee_id === $actorEmployeeId) {
            return 'self';
        }

        if ($actorEmployeeId !== null && $review->manager_employee_id === $actorEmployeeId) {
            return 'manager';
        }

        if (in_array($actor->id, $review->reviewer_user_ids ?? [], true)) {
            return 'reviewer';
        }

        return null;
    }

    public function canViewSubmission(User $actor, PerformanceReview $review, PerformanceReviewSubmission $submission): bool
    {
        $role = $this->determineActorRole($actor, $review);
        $rules = $review->visibility_rules ?? [];

        if ($role === 'hr') {
            return true;
        }

        if ($role === 'self') {
            if ($submission->role_type === 'self') {
                return true;
            }

            if ($submission->role_type === 'manager') {
                return $review->status === 'published'
                    || (bool) ($rules['employee_can_view_manager_assessment_before_publish'] ?? false);
            }

            if ($submission->role_type === 'reviewer') {
                return $review->status === 'published'
                    && (bool) ($rules['employee_can_view_peer_feedback_after_publish'] ?? false);
            }

            return false;
        }

        if ($role === 'manager') {
            if ($submission->role_type === 'reviewer') {
                return (bool) ($rules['manager_can_view_peer_feedback'] ?? true);
            }

            return in_array($submission->role_type, ['self', 'manager'], true);
        }

        if ($role === 'reviewer') {
            if ($submission->reviewer_user_id === $actor->id) {
                return true;
            }

            return $submission->role_type === 'reviewer'
                && (bool) ($rules['reviewer_can_view_other_reviewer_feedback'] ?? false);
        }

        return false;
    }

    public function shouldAnonymizeSubmissionForActor(User $actor, PerformanceReview $review, PerformanceReviewSubmission $submission): bool
    {
        $role = $this->determineActorRole($actor, $review);
        $rules = $review->visibility_rules ?? [];

        if ($submission->role_type !== 'reviewer') {
            return false;
        }

        if ($role === 'self') {
            return (bool) ($rules['peer_feedback_anonymous_to_employee'] ?? true);
        }

        return false;
    }

    public function ensureCompanyMatch(string $entity, bool $exists): void
    {
        if (! $exists) {
            throw (new ModelNotFoundException)->setModel($entity);
        }
    }
}
