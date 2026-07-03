<?php

namespace App\Modules\Platform\Release\Services;

use App\Models\ReleaseReadinessDecision;
use App\Models\User;
use App\Modules\Platform\Observability\Services\ObservabilityOverviewService;
use App\Modules\Platform\Resilience\Services\ResilienceReadinessService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReleaseReadinessService
{
    public function __construct(
        private readonly ReleaseQualityGateService $releaseQualityGateService,
        private readonly ObservabilityOverviewService $observabilityOverviewService,
        private readonly ResilienceReadinessService $resilienceReadinessService,
    ) {}

    public function overview(): array
    {
        $snapshot = $this->readinessSnapshot();
        $decisions = ReleaseReadinessDecision::query()
            ->with('decidedBy:id,name')
            ->orderByDesc('decided_at')
            ->orderByDesc('id')
            ->get();

        /** @var ReleaseReadinessDecision|null $latestDecision */
        $latestDecision = $decisions->first();
        $blockers = $this->buildBlockers($snapshot['areas'], $latestDecision);

        return [
            'summary' => [
                'total_area_count' => $snapshot['areas']->count(),
                'ready_area_count' => $snapshot['areas']->where('status', 'ready')->count(),
                'attention_area_count' => $snapshot['areas']->where('status', 'attention')->count(),
                'blocked_area_count' => $snapshot['areas']->where('status', 'blocked')->count(),
                'blocker_count' => $blockers->where('status', 'open')->count(),
                'runbook_count' => $snapshot['runbooks']->count(),
                'decision_count' => $decisions->count(),
                'latest_decision_at' => $latestDecision?->decided_at?->toIso8601String(),
            ],
            'policy' => $snapshot['policy'],
            'recommendation' => $this->buildRecommendation($snapshot['areas'], $latestDecision, $blockers),
            'areas' => $snapshot['areas']->all(),
            'blockers' => $blockers->all(),
            'runbooks' => $snapshot['runbooks']->all(),
            'latest_decision' => $latestDecision ? $this->serializeDecision($latestDecision) : null,
            'decision_history' => $decisions
                ->take(8)
                ->map(fn (ReleaseReadinessDecision $decision): array => $this->serializeDecision($decision))
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function recordDecision(User $actor, array $attributes): array
    {
        $snapshot = $this->readinessSnapshot();

        $decision = ReleaseReadinessDecision::query()->create([
            'release_window_label' => (string) $attributes['release_window_label'],
            'target_environment' => (string) $attributes['target_environment'],
            'decision_status' => (string) $attributes['decision_status'],
            'summary' => (string) $attributes['summary'],
            'blockers' => $this->normalizeDecisionBlockers($attributes['blockers'] ?? []),
            'artifact_refs' => array_values($attributes['artifact_refs'] ?? []),
            'checklist_snapshot' => $snapshot['areas']
                ->map(fn (array $area): array => [
                    'key' => $area['key'],
                    'name' => $area['name'],
                    'status' => $area['status'],
                    'blocking_item_count' => $area['blocking_item_count'],
                    'attention_item_count' => $area['attention_item_count'],
                    'last_reviewed_at' => $area['last_reviewed_at'],
                ])
                ->values()
                ->all(),
            'decision_notes' => $this->nullableString($attributes['decision_notes'] ?? null),
            'decided_at' => $this->resolveDecisionAt($attributes['decided_at'] ?? null),
            'decided_by_user_id' => $actor->id,
        ]);

        $decision->loadMissing('decidedBy:id,name');

        return $this->serializeDecision($decision);
    }

    /**
     * @return array{
     *     policy: array<string, mixed>,
     *     areas: Collection<int, array<string, mixed>>,
     *     runbooks: Collection<int, array<string, mixed>>
     * }
     */
    private function readinessSnapshot(): array
    {
        $releaseOverview = $this->releaseQualityGateService->overview();
        $resilienceOverview = $this->resilienceReadinessService->overview();
        $observabilityOverview = $this->observabilityOverviewService->overview();

        $gateIndex = collect($releaseOverview['gates'] ?? [])->keyBy('key');
        $scenarioIndex = collect($resilienceOverview['scenarios'] ?? [])->keyBy('key');
        $serviceIndex = collect($observabilityOverview['services'] ?? [])->keyBy('key');
        $workflowChecks = collect(config('release_readiness.workflow_verifications', []))
            ->map(fn (array $check): array => $this->normalizeWorkflowVerification($check))
            ->values();

        $areas = collect(config('release_readiness.areas', []))
            ->map(function (array $area) use ($gateIndex, $scenarioIndex, $serviceIndex, $workflowChecks): array {
                return match ((string) ($area['source'] ?? 'workflow_checks')) {
                    'release_gates' => $this->buildReleaseGateArea($area, $gateIndex),
                    'resilience_scenarios' => $this->buildResilienceArea($area, $scenarioIndex),
                    'observability_services' => $this->buildObservabilityArea($area, $serviceIndex),
                    default => $this->buildWorkflowArea($area, $workflowChecks),
                };
            })
            ->values();

        $runbooks = collect(config('release_readiness.runbooks', []))
            ->map(fn (array $runbook): array => $this->normalizeRunbook($runbook))
            ->values();

        return [
            'policy' => $this->normalizePolicy(config('release_readiness.policy', [])),
            'areas' => $areas,
            'runbooks' => $runbooks,
        ];
    }

    /**
     * @param  array<string, mixed>  $policy
     * @return array<string, mixed>
     */
    private function normalizePolicy(array $policy): array
    {
        return [
            'review_cadence' => (string) ($policy['review_cadence'] ?? ''),
            'decision_owner_roles' => array_values($policy['decision_owner_roles'] ?? []),
            'target_environments' => array_values($policy['target_environments'] ?? ['production']),
            'artifact_refs' => array_values($policy['artifact_refs'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $runbook
     * @return array<string, mixed>
     */
    private function normalizeRunbook(array $runbook): array
    {
        return [
            'key' => (string) ($runbook['key'] ?? 'runbook'),
            'name' => (string) ($runbook['name'] ?? 'Runbook'),
            'path' => (string) ($runbook['path'] ?? ''),
            'owner_role' => (string) ($runbook['owner_role'] ?? 'tenant.admin'),
            'summary' => (string) ($runbook['summary'] ?? ''),
            'when_to_use' => (string) ($runbook['when_to_use'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $area
     * @param  Collection<string, array<string, mixed>>  $gateIndex
     * @return array<string, mixed>
     */
    private function buildReleaseGateArea(array $area, Collection $gateIndex): array
    {
        $items = collect(array_values($area['gate_keys'] ?? []))
            ->map(function (string $gateKey) use ($gateIndex): ?array {
                $gate = $gateIndex->get($gateKey);

                if (! is_array($gate)) {
                    return null;
                }

                return [
                    'key' => (string) $gate['key'],
                    'label' => (string) $gate['name'],
                    'status' => $this->normalizeReleaseGateStatus((string) $gate['status']),
                    'owner_role' => (string) $gate['owner_role'],
                    'summary' => (string) $gate['summary'],
                    'last_reviewed_at' => $gate['last_run_at'] ?? null,
                    'artifact_refs' => array_values($gate['artifact_refs'] ?? []),
                ];
            })
            ->filter()
            ->values();

        return $this->buildAreaRecord($area, $items);
    }

    /**
     * @param  array<string, mixed>  $area
     * @param  Collection<string, array<string, mixed>>  $scenarioIndex
     * @return array<string, mixed>
     */
    private function buildResilienceArea(array $area, Collection $scenarioIndex): array
    {
        $items = collect(array_values($area['scenario_keys'] ?? []))
            ->map(function (string $scenarioKey) use ($scenarioIndex): ?array {
                $scenario = $scenarioIndex->get($scenarioKey);

                if (! is_array($scenario)) {
                    return null;
                }

                return [
                    'key' => (string) $scenario['key'],
                    'label' => (string) $scenario['name'],
                    'status' => match ((string) $scenario['status']) {
                        'ready' => 'ready',
                        'failed' => 'blocked',
                        default => 'attention',
                    },
                    'owner_role' => (string) $scenario['owner_role'],
                    'summary' => (string) $scenario['summary'],
                    'last_reviewed_at' => $scenario['last_validated_at'] ?? null,
                    'artifact_refs' => array_values($scenario['latest_run']['evidence_refs'] ?? []),
                ];
            })
            ->filter()
            ->values();

        return $this->buildAreaRecord($area, $items);
    }

    /**
     * @param  array<string, mixed>  $area
     * @param  Collection<string, array<string, mixed>>  $serviceIndex
     * @return array<string, mixed>
     */
    private function buildObservabilityArea(array $area, Collection $serviceIndex): array
    {
        $items = collect(array_values($area['service_keys'] ?? []))
            ->map(function (string $serviceKey) use ($serviceIndex): ?array {
                $service = $serviceIndex->get($serviceKey);

                if (! is_array($service)) {
                    return null;
                }

                return [
                    'key' => (string) $service['key'],
                    'label' => (string) $service['name'],
                    'status' => match ((string) $service['status']) {
                        'critical' => 'blocked',
                        'degraded' => 'attention',
                        default => 'ready',
                    },
                    'owner_role' => (string) $service['owner_role'],
                    'summary' => (string) $service['summary'],
                    'last_reviewed_at' => now()->toIso8601String(),
                    'artifact_refs' => [],
                ];
            })
            ->filter()
            ->values();

        return $this->buildAreaRecord($area, $items);
    }

    /**
     * @param  array<string, mixed>  $area
     * @param  Collection<int, array<string, mixed>>  $workflowChecks
     * @return array<string, mixed>
     */
    private function buildWorkflowArea(array $area, Collection $workflowChecks): array
    {
        return $this->buildAreaRecord($area, $workflowChecks);
    }

    /**
     * @param  array<string, mixed>  $area
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function buildAreaRecord(array $area, Collection $items): array
    {
        $blockingItemCount = $items->where('status', 'blocked')->count();
        $attentionItemCount = $items->where('status', 'attention')->count();
        $artifactRefs = $items
            ->flatMap(fn (array $item): array => array_values($item['artifact_refs'] ?? []))
            ->merge(array_values($area['artifact_refs'] ?? []))
            ->unique()
            ->values()
            ->all();

        return [
            'key' => (string) ($area['key'] ?? 'area'),
            'name' => (string) ($area['name'] ?? 'Readiness area'),
            'status' => $this->areaStatusFromItems($items),
            'source' => (string) ($area['source'] ?? 'workflow_checks'),
            'owner_role' => (string) ($area['owner_role'] ?? 'tenant.admin'),
            'summary' => (string) ($area['summary'] ?? ''),
            'evidence_requirements' => array_values($area['evidence_requirements'] ?? []),
            'artifact_refs' => $artifactRefs,
            'check_count' => $items->count(),
            'blocking_item_count' => $blockingItemCount,
            'attention_item_count' => $attentionItemCount,
            'last_reviewed_at' => $items->pluck('last_reviewed_at')->filter()->sort()->last(),
            'items' => $items->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $check
     * @return array<string, mixed>
     */
    private function normalizeWorkflowVerification(array $check): array
    {
        return [
            'key' => (string) ($check['key'] ?? 'workflow'),
            'label' => (string) ($check['label'] ?? 'Workflow verification'),
            'status' => match ((string) ($check['status'] ?? 'pending')) {
                'passing' => 'ready',
                'failed', 'blocked' => 'blocked',
                default => 'attention',
            },
            'owner_role' => (string) ($check['owner_role'] ?? 'tenant.admin'),
            'summary' => (string) ($check['summary'] ?? ''),
            'last_reviewed_at' => $check['last_reviewed_at'] ?? null,
            'artifact_refs' => array_values($check['artifact_refs'] ?? []),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    private function areaStatusFromItems(Collection $items): string
    {
        if ($items->contains(fn (array $item): bool => $item['status'] === 'blocked')) {
            return 'blocked';
        }

        if ($items->contains(fn (array $item): bool => $item['status'] === 'attention')) {
            return 'attention';
        }

        return 'ready';
    }

    private function normalizeReleaseGateStatus(string $status): string
    {
        return match ($status) {
            'passing' => 'ready',
            'warning' => 'attention',
            default => 'blocked',
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $areas
     */
    private function buildBlockers(Collection $areas, ?ReleaseReadinessDecision $latestDecision): Collection
    {
        $systemBlockers = $areas
            ->filter(fn (array $area): bool => $area['status'] !== 'ready')
            ->map(fn (array $area): array => [
                'key' => sprintf('system:%s', $area['key']),
                'area_key' => $area['key'],
                'area_name' => $area['name'],
                'title' => sprintf('%s still needs release review', $area['name']),
                'status' => 'open',
                'owner_role' => $area['owner_role'],
                'source' => 'system',
                'summary' => $this->systemBlockerSummary($area),
                'artifact_refs' => array_values($area['artifact_refs'] ?? []),
            ])
            ->values();

        $decisionBlockers = collect($latestDecision?->blockers ?? [])
            ->map(function (array $blocker) use ($areas): array {
                $areaKey = $this->nullableString($blocker['area_key'] ?? null);
                /** @var array<string, mixed>|null $area */
                $area = $areaKey ? $areas->first(fn (array $item): bool => $item['key'] === $areaKey) : null;

                return [
                    'key' => sprintf('decision:%s:%s', $areaKey ?? 'general', md5(json_encode($blocker))),
                    'area_key' => $areaKey,
                    'area_name' => is_array($area) ? (string) $area['name'] : null,
                    'title' => (string) ($blocker['title'] ?? 'Release blocker'),
                    'status' => (string) ($blocker['status'] ?? 'open'),
                    'owner_role' => (string) ($blocker['owner_role'] ?? 'tenant.admin'),
                    'source' => 'decision',
                    'summary' => $this->nullableString($blocker['notes'] ?? null) ?? 'Decision-scoped blocker recorded during go or no-go review.',
                    'artifact_refs' => [],
                ];
            })
            ->values();

        return $systemBlockers->concat($decisionBlockers)->values();
    }

    /**
     * @param  array<string, mixed>  $area
     */
    private function systemBlockerSummary(array $area): string
    {
        if ($area['status'] === 'blocked') {
            return sprintf(
                '%d blocking checklist item(s) still need remediation before launch approval can proceed.',
                max(1, (int) $area['blocking_item_count']),
            );
        }

        return sprintf(
            '%d checklist item(s) still need fresh evidence or operator acknowledgement before launch review is complete.',
            max(1, (int) $area['attention_item_count']),
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $areas
     * @param  Collection<int, array<string, mixed>>  $blockers
     * @return array<string, string>
     */
    private function buildRecommendation(
        Collection $areas,
        ?ReleaseReadinessDecision $latestDecision,
        Collection $blockers,
    ): array {
        if ($areas->contains(fn (array $area): bool => $area['status'] === 'blocked')) {
            return [
                'status' => 'no_go',
                'summary' => 'Blocking readiness areas still need remediation before launch approval can proceed.',
            ];
        }

        if ($latestDecision === null) {
            return [
                'status' => 'pending_review',
                'summary' => 'Checklist evidence is available, but no accountable go or no-go decision has been recorded yet.',
            ];
        }

        if ($latestDecision->decision_status === 'no_go') {
            return [
                'status' => 'no_go',
                'summary' => sprintf('Latest go-live review for %s is recorded as no-go.', $latestDecision->release_window_label),
            ];
        }

        if (
            $latestDecision->decision_status === 'conditional'
            || $areas->contains(fn (array $area): bool => $area['status'] === 'attention')
            || $blockers->contains(fn (array $blocker): bool => $blocker['status'] === 'open')
        ) {
            return [
                'status' => 'conditional',
                'summary' => 'Launch is conditionally approved only if the remaining evidence and blocker owners are actively tracked.',
            ];
        }

        return [
            'status' => 'go',
            'summary' => sprintf('Latest go-live review for %s is approved with no unresolved blockers.', $latestDecision->release_window_label),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blockers
     * @return array<int, array<string, mixed>>
     */
    private function normalizeDecisionBlockers(array $blockers): array
    {
        return collect($blockers)
            ->map(fn (array $blocker): array => [
                'area_key' => $this->nullableString($blocker['area_key'] ?? null),
                'title' => (string) ($blocker['title'] ?? 'Release blocker'),
                'owner_role' => (string) ($blocker['owner_role'] ?? 'tenant.admin'),
                'status' => (string) ($blocker['status'] ?? 'open'),
                'notes' => $this->nullableString($blocker['notes'] ?? null),
            ])
            ->values()
            ->all();
    }

    private function resolveDecisionAt(mixed $value): Carbon
    {
        return $value ? Carbon::parse((string) $value) : now();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDecision(ReleaseReadinessDecision $decision): array
    {
        return [
            'id' => $decision->id,
            'release_window_label' => $decision->release_window_label,
            'target_environment' => $decision->target_environment,
            'decision_status' => $decision->decision_status,
            'summary' => $decision->summary,
            'blockers' => array_values($decision->blockers ?? []),
            'artifact_refs' => array_values($decision->artifact_refs ?? []),
            'checklist_snapshot' => array_values($decision->checklist_snapshot ?? []),
            'decision_notes' => $decision->decision_notes,
            'decided_at' => $decision->decided_at?->toIso8601String(),
            'decided_by_user_id' => $decision->decided_by_user_id,
            'decided_by_name' => $decision->decidedBy?->name,
            'created_at' => $decision->created_at?->toIso8601String(),
            'updated_at' => $decision->updated_at?->toIso8601String(),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
