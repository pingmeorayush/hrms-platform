<?php

namespace App\Modules\Platform\Release\Services;

class ReleaseQualityGateService
{
    public function overview(): array
    {
        $policy = config('release.policy', []);
        $gates = collect(config('release.gates', []))
            ->map(fn (array $gate): array => $this->normalizeGate($gate))
            ->values();

        $gateIndex = $gates->keyBy('key');
        $environments = collect(config('release.environments', []))
            ->map(fn (array $environment): array => $this->normalizeEnvironment($environment, $gateIndex->all()))
            ->values();

        return [
            'summary' => [
                'total_gate_count' => $gates->count(),
                'blocking_gate_count' => $gates->where('blocking', true)->where('status', '!=', 'passing')->count(),
                'passing_gate_count' => $gates->where('status', 'passing')->count(),
                'pending_gate_count' => $gates->where('status', 'pending')->count() + $environments->where('status', 'pending')->count(),
                'warning_gate_count' => $gates->where('status', 'warning')->count(),
                'blocked_environment_count' => $environments->where('status', 'blocked')->count(),
                'protected_environment_count' => $environments->count(),
            ],
            'policy' => [
                'protected_branch' => $policy['protected_branch'] ?? 'main',
                'promotion_rule' => $policy['promotion_rule'] ?? '',
                'required_workflow_names' => array_values($policy['required_workflow_names'] ?? []),
                'reviewer_roles' => array_values($policy['reviewer_roles'] ?? []),
                'artifact_paths' => array_values($policy['artifact_paths'] ?? []),
            ],
            'gates' => $gates->all(),
            'environments' => $environments->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $gate
     * @return array<string, mixed>
     */
    private function normalizeGate(array $gate): array
    {
        $checks = collect($gate['checks'] ?? [])
            ->map(function (array $check): array {
                return [
                    'key' => (string) ($check['key'] ?? 'unknown'),
                    'label' => (string) ($check['label'] ?? 'Unnamed check'),
                    'status' => (string) ($check['status'] ?? 'pending'),
                    'command' => (string) ($check['command'] ?? ''),
                ];
            })
            ->values();

        return [
            'key' => (string) ($gate['key'] ?? 'unknown'),
            'name' => (string) ($gate['name'] ?? 'Unnamed gate'),
            'category' => (string) ($gate['category'] ?? 'governance'),
            'status' => (string) ($gate['status'] ?? 'pending'),
            'blocking' => (bool) ($gate['blocking'] ?? true),
            'owner_role' => (string) ($gate['owner_role'] ?? 'platform.support'),
            'workflow_name' => (string) ($gate['workflow_name'] ?? 'Release Quality Gates'),
            'workflow_path' => (string) ($gate['workflow_path'] ?? '.github/workflows/release-quality-gates.yml'),
            'summary' => (string) ($gate['summary'] ?? ''),
            'last_run_at' => $gate['last_run_at'] ?? null,
            'required_for' => array_values($gate['required_for'] ?? []),
            'artifact_refs' => array_values($gate['artifact_refs'] ?? []),
            'check_count' => $checks->count(),
            'failing_check_count' => $checks->where('status', '!=', 'passing')->count(),
            'checks' => $checks->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $environment
     * @param  array<string, array<string, mixed>>  $gateIndex
     * @return array<string, mixed>
     */
    private function normalizeEnvironment(array $environment, array $gateIndex): array
    {
        $requiredGateKeys = array_values($environment['required_gate_keys'] ?? []);
        $requiredGates = collect($requiredGateKeys)
            ->map(fn (string $key): ?array => $gateIndex[$key] ?? null)
            ->filter()
            ->values();

        $blockingGates = $requiredGates
            ->filter(fn (array $gate): bool => (bool) $gate['blocking'] && $gate['status'] !== 'passing')
            ->values();

        $manualApprovalRequired = (bool) ($environment['manual_approval_required'] ?? false);

        $status = 'passing';
        $blockedReason = null;

        if ($blockingGates->isNotEmpty()) {
            $status = 'blocked';
            $blockedReason = sprintf(
                'Promotion blocked until %s returns to passing state.',
                $blockingGates->pluck('name')->implode(', '),
            );
        } elseif ($manualApprovalRequired) {
            $status = 'pending';
            $blockedReason = $environment['manual_approval_label']
                ?? 'Manual approval is still required before this promotion can proceed.';
        }

        return [
            'key' => (string) ($environment['key'] ?? 'environment'),
            'name' => (string) ($environment['name'] ?? 'Environment'),
            'status' => $status,
            'manual_approval_required' => $manualApprovalRequired,
            'required_gate_keys' => $requiredGateKeys,
            'required_gate_count' => $requiredGates->count(),
            'blocking_gate_count' => $blockingGates->count(),
            'blocked_reason' => $blockedReason,
        ];
    }
}
