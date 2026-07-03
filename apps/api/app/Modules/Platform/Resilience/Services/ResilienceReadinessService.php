<?php

namespace App\Modules\Platform\Resilience\Services;

use App\Models\ResilienceValidationRun;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ResilienceReadinessService
{
    public function overview(): array
    {
        $policy = $this->normalizePolicy(config('resilience.policy', []));
        $runbook = collect(config('resilience.runbook', []))
            ->map(fn (array $step): array => $this->normalizeRunbookStep($step))
            ->sortBy('sequence')
            ->values();

        $scenarioIndex = collect(config('resilience.scenarios', []))
            ->map(fn (array $scenario, string $key): array => $this->normalizeScenarioConfig($key, $scenario))
            ->keyBy('key');

        $validationRuns = ResilienceValidationRun::query()
            ->with('executedBy:id,name')
            ->orderByDesc('completed_at')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->get();

        $latestRunByScenario = $validationRuns
            ->groupBy('scenario_key')
            ->map(fn (Collection $runs): ?ResilienceValidationRun => $runs->first());

        $scenarios = $scenarioIndex
            ->values()
            ->map(fn (array $scenario): array => $this->buildScenarioReadiness($scenario, $latestRunByScenario->get($scenario['key'])))
            ->values();

        return [
            'summary' => [
                'total_scenario_count' => $scenarios->count(),
                'ready_scenario_count' => $scenarios->where('status', 'ready')->count(),
                'attention_scenario_count' => $scenarios->where('status', 'attention')->count(),
                'failed_scenario_count' => $scenarios->where('status', 'failed')->count(),
                'overdue_scenario_count' => $scenarios->where('overdue', true)->count(),
                'validation_run_count' => $validationRuns->count(),
                'latest_validation_at' => $this->latestValidationAt($validationRuns),
            ],
            'policy' => $policy,
            'scenarios' => $scenarios->all(),
            'runbook' => $runbook->all(),
            'validation_runs' => $validationRuns
                ->take(12)
                ->map(fn (ResilienceValidationRun $run): array => $this->serializeRun($run))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function recordValidationRun(User $actor, array $attributes): array
    {
        $scenario = $this->scenarioConfig((string) $attributes['scenario_key']);
        $startedAt = $this->resolveStartedAt($attributes['started_at'] ?? null);
        $completedAt = $this->resolveCompletedAt($attributes['status'], $startedAt, $attributes['completed_at'] ?? null);

        $run = ResilienceValidationRun::query()->create([
            'scenario_key' => $scenario['key'],
            'scenario_name' => $scenario['name'],
            'scenario_type' => $scenario['scenario_type'],
            'environment' => $scenario['environment'],
            'status' => (string) $attributes['status'],
            'recovery_point_actual_minutes' => $attributes['recovery_point_actual_minutes'] ?? null,
            'recovery_time_actual_minutes' => $attributes['recovery_time_actual_minutes'] ?? null,
            'evidence_refs' => array_values($attributes['evidence_refs'] ?? []),
            'notes' => $attributes['notes'] ?? null,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'executed_by_user_id' => $actor->id,
        ]);

        $run->loadMissing('executedBy:id,name');

        return $this->serializeRun($run);
    }

    /**
     * @param  array<string, mixed>  $policy
     * @return array<string, mixed>
     */
    private function normalizePolicy(array $policy): array
    {
        return [
            'primary_region' => (string) ($policy['primary_region'] ?? 'primary'),
            'secondary_region' => (string) ($policy['secondary_region'] ?? 'secondary'),
            'backup_cadence' => (string) ($policy['backup_cadence'] ?? ''),
            'restore_validation_cadence' => (string) ($policy['restore_validation_cadence'] ?? ''),
            'dr_drill_cadence' => (string) ($policy['dr_drill_cadence'] ?? ''),
            'retention_policy' => (string) ($policy['retention_policy'] ?? ''),
            'encryption_posture' => (string) ($policy['encryption_posture'] ?? ''),
            'coverage_scope' => (string) ($policy['coverage_scope'] ?? ''),
            'default_rpo_minutes' => (int) ($policy['default_rpo_minutes'] ?? 60),
            'default_rto_minutes' => (int) ($policy['default_rto_minutes'] ?? 240),
            'artifact_refs' => array_values($policy['artifact_refs'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $step
     * @return array<string, mixed>
     */
    private function normalizeRunbookStep(array $step): array
    {
        return [
            'key' => (string) ($step['key'] ?? 'step'),
            'name' => (string) ($step['name'] ?? 'Runbook step'),
            'sequence' => (int) ($step['sequence'] ?? 0),
            'owner_role' => (string) ($step['owner_role'] ?? 'tenant.admin'),
            'objective' => (string) ($step['objective'] ?? ''),
            'evidence_requirements' => array_values($step['evidence_requirements'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $scenario
     * @return array<string, mixed>
     */
    private function normalizeScenarioConfig(string $key, array $scenario): array
    {
        return [
            'key' => $key,
            'name' => (string) ($scenario['name'] ?? 'Scenario'),
            'scenario_type' => (string) ($scenario['scenario_type'] ?? 'restore'),
            'environment' => (string) ($scenario['environment'] ?? 'production'),
            'owner_role' => (string) ($scenario['owner_role'] ?? 'tenant.admin'),
            'cadence_days' => (int) ($scenario['cadence_days'] ?? 30),
            'recovery_point_objective_minutes' => (int) ($scenario['recovery_point_objective_minutes'] ?? config('resilience.policy.default_rpo_minutes', 60)),
            'recovery_time_objective_minutes' => (int) ($scenario['recovery_time_objective_minutes'] ?? config('resilience.policy.default_rto_minutes', 240)),
            'summary' => (string) ($scenario['summary'] ?? ''),
            'evidence_requirements' => array_values($scenario['evidence_requirements'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $scenario
     * @return array<string, mixed>
     */
    private function buildScenarioReadiness(array $scenario, ?ResilienceValidationRun $run): array
    {
        $latestValidationAt = $run?->completed_at ?? $run?->started_at;
        $nextValidationDueAt = $latestValidationAt?->copy()->addDays((int) $scenario['cadence_days']);
        $overdue = $run === null || ($nextValidationDueAt !== null && $nextValidationDueAt->isPast());

        $status = match ($run?->status) {
            'failed' => 'failed',
            'issues_found', 'in_progress' => 'attention',
            'passed' => $overdue ? 'attention' : 'ready',
            default => 'attention',
        };

        $blockedReason = match (true) {
            $run === null => 'No validation run has been recorded yet for this recovery scenario.',
            $run->status === 'failed' => 'The latest validation failed and recovery readiness is not approved.',
            $run->status === 'issues_found' => 'The latest validation found issues that still require remediation and retest evidence.',
            $run->status === 'in_progress' => 'Validation is still in progress and evidence collection is not complete yet.',
            $overdue => 'The latest successful validation is older than the agreed cadence and must be rerun.',
            default => null,
        };

        return [
            'key' => $scenario['key'],
            'name' => $scenario['name'],
            'scenario_type' => $scenario['scenario_type'],
            'environment' => $scenario['environment'],
            'owner_role' => $scenario['owner_role'],
            'cadence_days' => $scenario['cadence_days'],
            'recovery_point_objective_minutes' => $scenario['recovery_point_objective_minutes'],
            'recovery_time_objective_minutes' => $scenario['recovery_time_objective_minutes'],
            'status' => $status,
            'summary' => $scenario['summary'],
            'overdue' => $overdue,
            'blocked_reason' => $blockedReason,
            'last_validated_at' => $latestValidationAt?->toIso8601String(),
            'next_validation_due_at' => $nextValidationDueAt?->toIso8601String(),
            'evidence_requirements' => $scenario['evidence_requirements'],
            'latest_run' => $run ? $this->serializeRun($run) : null,
        ];
    }

    private function scenarioConfig(string $scenarioKey): array
    {
        /** @var array<string, mixed>|null $scenario */
        $scenario = config("resilience.scenarios.{$scenarioKey}");

        if (! is_array($scenario)) {
            abort(422, 'Unknown resilience scenario.');
        }

        return $this->normalizeScenarioConfig($scenarioKey, $scenario);
    }

    private function resolveStartedAt(mixed $value): Carbon
    {
        return $value ? Carbon::parse((string) $value) : now();
    }

    private function resolveCompletedAt(string $status, Carbon $startedAt, mixed $value): ?Carbon
    {
        if ($status === 'in_progress') {
            return null;
        }

        return $value ? Carbon::parse((string) $value) : $startedAt->copy();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRun(ResilienceValidationRun $run): array
    {
        return [
            'id' => $run->id,
            'scenario_key' => $run->scenario_key,
            'scenario_name' => $run->scenario_name,
            'scenario_type' => $run->scenario_type,
            'environment' => $run->environment,
            'status' => $run->status,
            'recovery_point_actual_minutes' => $run->recovery_point_actual_minutes,
            'recovery_time_actual_minutes' => $run->recovery_time_actual_minutes,
            'evidence_refs' => array_values($run->evidence_refs ?? []),
            'notes' => $run->notes,
            'started_at' => $run->started_at?->toIso8601String(),
            'completed_at' => $run->completed_at?->toIso8601String(),
            'executed_by_user_id' => $run->executed_by_user_id,
            'executed_by_name' => $run->executedBy?->name,
            'created_at' => $run->created_at?->toIso8601String(),
            'updated_at' => $run->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, ResilienceValidationRun>  $runs
     */
    private function latestValidationAt(Collection $runs): ?string
    {
        /** @var ResilienceValidationRun|null $latest */
        $latest = $runs->first();

        return $latest?->completed_at?->toIso8601String() ?? $latest?->started_at?->toIso8601String();
    }
}
