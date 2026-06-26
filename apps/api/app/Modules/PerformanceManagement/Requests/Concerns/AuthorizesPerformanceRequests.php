<?php

namespace App\Modules\PerformanceManagement\Requests\Concerns;

use App\Models\PerformanceReview;
use App\Models\User;
use App\Modules\PerformanceManagement\Services\PerformanceAccessScopeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait AuthorizesPerformanceRequests
{
    protected function performanceUser(): ?User
    {
        $user = $this->user();

        return $user instanceof User ? $user : null;
    }

    protected function canAccessPerformanceWorkspace(): bool
    {
        $user = $this->performanceUser();

        return $user?->canAny([
            'performance.view',
            'performance.review',
            'performance.manage',
            'performance.calibrate',
        ]) ?? false;
    }

    protected function canManagePerformance(): bool
    {
        return $this->performanceUser()?->can('performance.manage') ?? false;
    }

    protected function canAdministerPerformance(): bool
    {
        $user = $this->performanceUser();

        return $user?->canAny(['performance.manage', 'performance.calibrate']) ?? false;
    }

    protected function canSubmitPerformanceReview(): bool
    {
        if ($this->canAdministerPerformance()) {
            return true;
        }

        $user = $this->performanceUser();
        $review = $user ? $this->resolveAccessiblePerformanceReview($user) : null;

        if (! $review) {
            return false;
        }

        return app(PerformanceAccessScopeService::class)->determineActorRole($user, $review) !== null;
    }

    private function resolveAccessiblePerformanceReview(User $user): ?PerformanceReview
    {
        $reviewId = $this->route('performanceReviewId');

        if (! is_numeric($reviewId)) {
            return null;
        }

        try {
            return app(PerformanceAccessScopeService::class)->resolveAccessibleReview($user, (int) $reviewId);
        } catch (ModelNotFoundException) {
            return null;
        }
    }
}
