<?php

namespace App\Modules\Platform\Observability\Services;

use App\Models\IntegrationConnection;
use App\Models\IntegrationSyncJob;
use App\Models\NotificationRecord;
use App\Models\PayrollRun;
use App\Models\ReportExport;
use App\Models\ReportSubscription;
use App\Models\WebhookSubscription;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowTask;
use App\Modules\Platform\Release\Services\ReleaseQualityGateService;
use Illuminate\Support\Collection;

class ObservabilityOverviewService
{
    public function __construct(private readonly ReleaseQualityGateService $releaseQualityGateService) {}

    public function overview(): array
    {
        $observedAt = now()->toIso8601String();
        $releaseOverview = $this->releaseQualityGateService->overview();
        $releaseSummary = is_array($releaseOverview['summary'] ?? null) ? $releaseOverview['summary'] : [];

        $alertRouteIndex = collect(config('observability.alert_routes', []))
            ->map(fn (array $route): array => $this->normalizeAlertRoute($route))
            ->keyBy('key');

        $signals = collect(config('observability.signals', []))
            ->map(fn (array $signal): array => $this->buildSignal($signal, $releaseSummary, $alertRouteIndex, $observedAt))
            ->values();
        $signalIndex = $signals->keyBy('key');

        $services = collect(config('observability.services', []))
            ->map(fn (array $service): array => $this->buildService($service, $signalIndex))
            ->values();
        $serviceIndex = $services->keyBy('key');

        $alerts = $signals
            ->filter(fn (array $signal): bool => $signal['severity'] !== null)
            ->map(fn (array $signal): array => $this->buildAlert($signal, $serviceIndex))
            ->values();

        $coverage = [
            'workflows' => collect(config('observability.coverage.workflows', []))
                ->map(fn (array $item): array => $this->buildWorkflowCoverage($item))
                ->values()
                ->all(),
            'integrations' => collect(config('observability.coverage.integrations', []))
                ->map(fn (array $item): array => $this->buildIntegrationCoverage($item))
                ->values()
                ->all(),
            'release_critical' => collect(config('observability.coverage.release_critical', []))
                ->map(fn (array $item): array => $this->buildReleaseCoverage($item, $releaseSummary))
                ->values()
                ->all(),
        ];

        return [
            'summary' => [
                'service_count' => $services->count(),
                'healthy_service_count' => $services->where('status', 'healthy')->count(),
                'degraded_service_count' => $services->where('status', 'degraded')->count(),
                'critical_service_count' => $services->where('status', 'critical')->count(),
                'active_alert_count' => $alerts->count(),
                'routed_alert_count' => $alerts->whereNotNull('route_key')->count(),
                'monitored_workflow_count' => count($coverage['workflows']),
                'monitored_integration_count' => count($coverage['integrations']),
                'release_critical_coverage_count' => count($coverage['release_critical']),
            ],
            'telemetry' => [
                'health_endpoint' => (string) config('observability.telemetry.health_endpoint', '/up'),
                'default_log_channel' => (string) config('logging.default', 'stack'),
                'slack_alert_channel' => $this->nullableString(config('services.slack.notifications.channel')),
                'dashboard_refresh_minutes' => (int) config('observability.telemetry.dashboard_refresh_minutes', 5),
                'required_release_workflows' => array_values(config('release.policy.required_workflow_names', [])),
            ],
            'services' => $services->all(),
            'signals' => $signals->all(),
            'alerts' => $alerts->all(),
            'alert_routes' => $alertRouteIndex->values()->all(),
            'coverage' => $coverage,
        ];
    }

    /**
     * @param  array<string, mixed>  $service
     * @param  Collection<string, array<string, mixed>>  $signalIndex
     * @return array<string, mixed>
     */
    private function buildService(array $service, Collection $signalIndex): array
    {
        $signalKeys = array_values($service['signal_keys'] ?? []);
        $serviceSignals = collect($signalKeys)
            ->map(fn (string $key): ?array => $signalIndex->get($key))
            ->filter()
            ->values();

        $status = 'healthy';

        if ($serviceSignals->contains(fn (array $signal): bool => $signal['status'] === 'critical')) {
            $status = 'critical';
        } elseif ($serviceSignals->contains(fn (array $signal): bool => $signal['status'] === 'warning')) {
            $status = 'degraded';
        }

        return [
            'key' => (string) ($service['key'] ?? 'service'),
            'name' => (string) ($service['name'] ?? 'Service'),
            'category' => (string) ($service['category'] ?? 'platform'),
            'owner_role' => (string) ($service['owner_role'] ?? 'platform.support'),
            'status' => $status,
            'summary' => (string) ($service['summary'] ?? ''),
            'signal_keys' => $signalKeys,
            'alert_count' => $serviceSignals->filter(fn (array $signal): bool => $signal['severity'] !== null)->count(),
            'metric_count' => $serviceSignals->count(),
            'metrics' => $serviceSignals->map(fn (array $signal): array => [
                'key' => $signal['key'],
                'label' => $signal['name'],
                'value' => $signal['value'],
                'threshold' => $signal['threshold'],
                'unit' => $signal['unit'],
                'status' => $signal['status'],
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $signal
     * @param  array<string, mixed>  $releaseSummary
     * @param  Collection<string, array<string, mixed>>  $alertRouteIndex
     * @return array<string, mixed>
     */
    private function buildSignal(array $signal, array $releaseSummary, Collection $alertRouteIndex, string $observedAt): array
    {
        $value = $this->signalValue((string) ($signal['key'] ?? 'unknown'), $releaseSummary, $signal);
        $warningThreshold = $this->nullableInt($signal['warning_threshold'] ?? null);
        $criticalThreshold = $this->nullableInt($signal['critical_threshold'] ?? null);
        $status = $this->signalStatus($value, $warningThreshold, $criticalThreshold);

        $routeKey = match ($status) {
            'critical' => $this->nullableString($signal['critical_route_key'] ?? $signal['warning_route_key'] ?? null),
            'warning' => $this->nullableString($signal['warning_route_key'] ?? null),
            default => null,
        };
        $route = $routeKey ? $alertRouteIndex->get($routeKey) : null;

        return [
            'key' => (string) ($signal['key'] ?? 'unknown'),
            'name' => (string) ($signal['name'] ?? 'Signal'),
            'category' => (string) ($signal['category'] ?? 'platform'),
            'service_key' => (string) ($signal['service_key'] ?? 'core_api'),
            'status' => $status,
            'severity' => is_array($route) ? (string) $route['severity'] : null,
            'owner_role' => (string) ($signal['owner_role'] ?? 'platform.support'),
            'value' => $value,
            'threshold' => $status === 'critical' ? $criticalThreshold : $warningThreshold,
            'unit' => (string) ($signal['unit'] ?? 'count'),
            'summary' => $this->signalSummary((string) ($signal['key'] ?? 'unknown'), $value, $signal, $releaseSummary),
            'observed_at' => $observedAt,
            'route_key' => is_array($route) ? (string) $route['key'] : null,
            'route_name' => is_array($route) ? (string) $route['name'] : null,
            'route_channels' => is_array($route) ? array_values($route['channels']) : [],
            'drill_in_label' => (string) ($signal['drill_in_label'] ?? 'Open workspace'),
            'drill_in_path' => (string) ($signal['drill_in_path'] ?? '/operations'),
        ];
    }

    /**
     * @param  array<string, mixed>  $signal
     * @param  Collection<string, array<string, mixed>>  $serviceIndex
     * @return array<string, mixed>
     */
    private function buildAlert(array $signal, Collection $serviceIndex): array
    {
        $service = $serviceIndex->get((string) $signal['service_key']);

        return [
            'key' => (string) $signal['key'],
            'title' => $this->alertTitle((string) $signal['key']),
            'severity' => $signal['severity'],
            'service_key' => $signal['service_key'],
            'service_name' => is_array($service) ? (string) $service['name'] : (string) $signal['service_key'],
            'signal_key' => $signal['key'],
            'status' => 'active',
            'owner_role' => $signal['owner_role'],
            'route_key' => $signal['route_key'],
            'route_name' => $signal['route_name'],
            'channels' => array_values($signal['route_channels']),
            'summary' => $signal['summary'],
            'started_at' => $signal['observed_at'],
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function buildWorkflowCoverage(array $item): array
    {
        $workflowKey = (string) ($item['workflow_key'] ?? '');
        $issueCount = $this->countOverdueWorkflowTasksForWorkflowKey($workflowKey);
        $monitoredEntityCount = WorkflowDefinition::query()
            ->where('key', $workflowKey)
            ->where('status', 'published')
            ->count();

        return [
            'key' => (string) ($item['key'] ?? $workflowKey),
            'name' => (string) ($item['name'] ?? 'Workflow coverage'),
            'area' => 'workflow',
            'owner_role' => (string) ($item['owner_role'] ?? 'workflow.monitor'),
            'coverage_state' => $issueCount > 0 ? 'attention' : 'monitored',
            'monitored_entity_count' => $monitoredEntityCount,
            'issue_count' => $issueCount,
            'signal_keys' => array_values($item['signal_keys'] ?? []),
            'summary' => (string) ($item['summary'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function buildIntegrationCoverage(array $item): array
    {
        $systemKey = (string) ($item['system_key'] ?? '');
        $eventKeys = array_values($item['event_keys'] ?? []);
        $staleAfterMinutes = $this->nullableInt($item['stale_after_minutes'] ?? null) ?? 20;

        $monitoredEntityCount = WebhookSubscription::query()
            ->where('status', 'active')
            ->whereIn('event_key', $eventKeys)
            ->whereHas('connection', fn ($query) => $query->where('system_key', $systemKey)->where('status', 'active'))
            ->count();

        if ($monitoredEntityCount === 0) {
            $monitoredEntityCount = IntegrationConnection::query()
                ->where('system_key', $systemKey)
                ->where('status', 'active')
                ->count();
        }

        $issueCount = IntegrationSyncJob::query()
            ->where('system_key', $systemKey)
            ->whereIn('event_key', $eventKeys)
            ->where(function ($query) use ($staleAfterMinutes): void {
                $query->where('status', 'failed')
                    ->orWhere(function ($queued) use ($staleAfterMinutes): void {
                        $queued
                            ->where('status', 'queued')
                            ->where('queued_at', '<', now()->subMinutes($staleAfterMinutes));
                    });
            })
            ->count();

        return [
            'key' => (string) ($item['key'] ?? $systemKey),
            'name' => (string) ($item['name'] ?? 'Integration coverage'),
            'area' => 'integration',
            'owner_role' => (string) ($item['owner_role'] ?? 'integration.manage'),
            'coverage_state' => $issueCount > 0 || $monitoredEntityCount === 0 ? 'attention' : 'monitored',
            'monitored_entity_count' => $monitoredEntityCount,
            'issue_count' => $issueCount,
            'signal_keys' => array_values($item['signal_keys'] ?? []),
            'summary' => (string) ($item['summary'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $releaseSummary
     * @return array<string, mixed>
     */
    private function buildReleaseCoverage(array $item, array $releaseSummary): array
    {
        $kind = (string) ($item['kind'] ?? 'gates');
        $monitoredEntityCount = $kind === 'environments'
            ? (int) ($releaseSummary['protected_environment_count'] ?? 0)
            : (int) ($releaseSummary['total_gate_count'] ?? 0);
        $issueCount = $kind === 'environments'
            ? (int) ($releaseSummary['blocked_environment_count'] ?? 0)
            : (int) ($releaseSummary['blocking_gate_count'] ?? 0);

        return [
            'key' => (string) ($item['key'] ?? $kind),
            'name' => (string) ($item['name'] ?? 'Release coverage'),
            'area' => 'release_critical',
            'owner_role' => (string) ($item['owner_role'] ?? 'release.manage'),
            'coverage_state' => $issueCount > 0 ? 'attention' : 'monitored',
            'monitored_entity_count' => $monitoredEntityCount,
            'issue_count' => $issueCount,
            'signal_keys' => array_values($item['signal_keys'] ?? []),
            'summary' => (string) ($item['summary'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $route
     * @return array<string, mixed>
     */
    private function normalizeAlertRoute(array $route): array
    {
        return [
            'key' => (string) ($route['key'] ?? 'route'),
            'severity' => (string) ($route['severity'] ?? 'sev3'),
            'name' => (string) ($route['name'] ?? 'Alert route'),
            'owner_team' => (string) ($route['owner_team'] ?? 'platform.support'),
            'channels' => array_values($route['channels'] ?? []),
            'initial_response_minutes' => (int) ($route['initial_response_minutes'] ?? 60),
            'escalation_minutes' => (int) ($route['escalation_minutes'] ?? 240),
        ];
    }

    /**
     * @param  array<string, mixed>  $releaseSummary
     * @param  array<string, mixed>  $signal
     */
    private function signalValue(string $key, array $releaseSummary, array $signal): int
    {
        return match ($key) {
            'integration_failed_jobs' => IntegrationSyncJob::query()->where('status', 'failed')->count(),
            'integration_stale_queue' => IntegrationSyncJob::query()
                ->where('status', 'queued')
                ->where('queued_at', '<', now()->subMinutes($this->nullableInt($signal['stale_after_minutes'] ?? null) ?? 20))
                ->count(),
            'workflow_overdue_tasks' => WorkflowTask::query()
                ->where('status', 'open')
                ->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->count(),
            'payroll_blocked_runs' => PayrollRun::query()->whereIn('status', ['blocked', 'failed'])->count(),
            'notification_failed_delivery' => NotificationRecord::query()->where('delivery_status', 'failed')->count(),
            'report_failed_exports' => ReportExport::query()->where('status', 'failed')->count(),
            'report_blocked_subscriptions' => ReportSubscription::query()->whereIn('status', ['blocked', 'paused'])->count(),
            'release_blocking_gates' => (int) ($releaseSummary['blocking_gate_count'] ?? 0),
            default => 0,
        };
    }

    private function signalStatus(int $value, ?int $warningThreshold, ?int $criticalThreshold): string
    {
        if ($criticalThreshold !== null && $value >= $criticalThreshold) {
            return 'critical';
        }

        if ($warningThreshold !== null && $value >= $warningThreshold) {
            return 'warning';
        }

        return 'healthy';
    }

    /**
     * @param  array<string, mixed>  $signal
     * @param  array<string, mixed>  $releaseSummary
     */
    private function signalSummary(string $key, int $value, array $signal, array $releaseSummary): string
    {
        return match ($key) {
            'integration_failed_jobs' => $value === 0
                ? 'No failed integration sync jobs are awaiting operator retry.'
                : sprintf('%d integration sync job(s) failed and require retry or payload review.', $value),
            'integration_stale_queue' => $value === 0
                ? 'No queued integration jobs have breached the stale-delivery threshold.'
                : sprintf(
                    '%d queued integration job(s) are older than %d minutes and need processing.',
                    $value,
                    $this->nullableInt($signal['stale_after_minutes'] ?? null) ?? 20,
                ),
            'workflow_overdue_tasks' => $value === 0
                ? 'No open workflow tasks are overdue against configured SLA targets.'
                : sprintf('%d workflow task(s) are overdue against stage SLA targets.', $value),
            'payroll_blocked_runs' => $value === 0
                ? 'No payroll runs are blocked or failed.'
                : sprintf('%d payroll run(s) are blocked or failed ahead of calculation or approval.', $value),
            'notification_failed_delivery' => $value === 0
                ? 'Notification delivery is clear of failed sends.'
                : sprintf('%d notification delivery failure(s) still need retry or channel review.', $value),
            'report_failed_exports' => $value === 0
                ? 'No report exports are currently failed.'
                : sprintf('%d report export(s) failed before governed delivery completed.', $value),
            'report_blocked_subscriptions' => $value === 0
                ? 'No report subscriptions are blocked or paused.'
                : sprintf('%d report subscription(s) are blocked or paused and need governance review.', $value),
            'release_blocking_gates' => $value === 0
                ? sprintf(
                    'All %d blocking release gate(s) are currently passing.',
                    (int) ($releaseSummary['total_gate_count'] ?? 0),
                )
                : sprintf('%d blocking release gate(s) are preventing protected promotion.', $value),
            default => sprintf('%d issue(s) observed for %s.', $value, str_replace('_', ' ', $key)),
        };
    }

    private function alertTitle(string $signalKey): string
    {
        return match ($signalKey) {
            'integration_failed_jobs' => 'Integration delivery failures are accumulating',
            'integration_stale_queue' => 'Integration queue breached delivery SLA',
            'workflow_overdue_tasks' => 'Workflow approvals breached SLA',
            'payroll_blocked_runs' => 'Payroll control lane is blocked',
            'notification_failed_delivery' => 'Notification delivery needs recovery',
            'report_failed_exports' => 'Report export delivery failed',
            'report_blocked_subscriptions' => 'Report subscriptions are blocked',
            'release_blocking_gates' => 'Release promotion is blocked',
            default => 'Observability alert',
        };
    }

    private function countOverdueWorkflowTasksForWorkflowKey(string $workflowKey): int
    {
        return WorkflowTask::query()
            ->where('status', 'open')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereHas('instance.definition', fn ($query) => $query->where('key', $workflowKey))
            ->count();
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
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
