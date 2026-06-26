<?php

namespace App\Modules\LearningManagement\Services;

use App\Models\LearningAssignmentTarget;
use Carbon\CarbonInterface;

/**
 * @phpstan-type LearningTargetSummary array{
 *   due_state: string,
 *   renewal_posture: string,
 *   evidence_present: bool,
 *   requires_completion_evidence: bool,
 *   renewal_frequency_months: int|null
 * }
 */
class LearningTrackingStateResolver
{
    public function dueState(?CarbonInterface $dueOn, ?CarbonInterface $completedAt): string
    {
        if ($completedAt !== null) {
            return 'completed';
        }

        if ($dueOn === null) {
            return 'no_due_date';
        }

        $today = now()->startOfDay();

        if ($dueOn->lt($today)) {
            return 'overdue';
        }

        if ($dueOn->equalTo($today)) {
            return 'due_today';
        }

        return 'upcoming';
    }

    public function renewalPosture(?int $renewalFrequencyMonths, ?CarbonInterface $completedAt, ?CarbonInterface $renewalDueOn): string
    {
        if ($renewalFrequencyMonths === null) {
            return 'not_configured';
        }

        if ($completedAt === null || $renewalDueOn === null) {
            return 'pending_initial_completion';
        }

        $today = now()->startOfDay();

        if ($renewalDueOn->lt($today)) {
            return 'overdue';
        }

        if ($renewalDueOn->equalTo($today)) {
            return 'due_today';
        }

        return 'current';
    }

    /**
     * @return LearningTargetSummary
     */
    public function summarizeTarget(LearningAssignmentTarget $target): array
    {
        $renewalFrequencyMonths = $this->renewalFrequencyMonths($target);

        return [
            'due_state' => $this->dueState($target->due_on, $target->completed_at),
            'renewal_posture' => $this->renewalPosture(
                $renewalFrequencyMonths,
                $target->completed_at,
                $target->renewal_due_on,
            ),
            'evidence_present' => ! empty($target->completion_evidence),
            'requires_completion_evidence' => $this->requiresCompletionEvidence($target),
            'renewal_frequency_months' => $renewalFrequencyMonths,
        ];
    }

    public function requiresCompletionEvidence(LearningAssignmentTarget $target): bool
    {
        return (bool) data_get($target->assignment?->completion_rules, 'requires_completion_evidence', false);
    }

    public function renewalFrequencyMonths(LearningAssignmentTarget $target): ?int
    {
        $value = data_get($target->assignment?->completion_rules, 'renewal_frequency_months');

        return $value === null ? null : (int) $value;
    }
}
