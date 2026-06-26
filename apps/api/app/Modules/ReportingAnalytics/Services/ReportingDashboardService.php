<?php

namespace App\Modules\ReportingAnalytics\Services;

use App\Models\DashboardSnapshot;
use App\Models\DashboardWidget;
use App\Models\Employee;
use App\Models\KpiDefinition;
use App\Models\LearningAssignmentTarget;
use App\Models\LeaveRequest;
use App\Models\ReportDataset;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @phpstan-type DashboardWidgetDefinition array{
 *   key: string,
 *   name: string,
 *   widget_type: string,
 *   description: string,
 *   position: int,
 *   kpi_key: string,
 *   dataset_key: string,
 *   aggregate_key: string,
 *   default_drilldown_path: string|null,
 *   freshness_expectation_minutes: int
 * }
 * @phpstan-type DashboardDefinition array{
 *   key: string,
 *   name: string,
 *   persona: string,
 *   description: string,
 *   widgets: list<DashboardWidgetDefinition>
 * }
 */
class ReportingDashboardService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly ReportingAccessScopeService $accessScopeService,
    ) {}

    /**
     * @return array{
     *   dashboard: array<string, mixed>,
     *   snapshot: array<string, mixed>,
     *   freshness: array<string, mixed>,
     *   widgets: list<array<string, mixed>>
     * }
     */
    public function show(User $actor, string $dashboardKey, bool $forceRefresh = false): array
    {
        $dashboard = $this->resolveAccessibleDashboard($actor, $dashboardKey);
        $widgetRecords = $this->syncDashboardWidgets($actor, $dashboard);
        $scopeSignature = $this->scopeSignature($actor, $dashboard['key']);
        $sourceSignature = $this->sourceSignature($widgetRecords);

        if (! $forceRefresh) {
            $cachedSnapshot = DashboardSnapshot::query()
                ->where('company_id', $actor->company_id)
                ->where('dashboard_key', $dashboard['key'])
                ->where('scope_signature', $scopeSignature)
                ->where('source_signature', $sourceSignature)
                ->where('expires_at', '>', now())
                ->latest('generated_at')
                ->first();

            if ($cachedSnapshot) {
                $payload = $this->payloadFromSnapshot($cachedSnapshot, true);
                $this->recordDashboardViewAudit($actor, $dashboard['key'], $cachedSnapshot, true, count($payload['widgets']));

                return $payload;
            }
        }

        $generatedPayload = $this->buildDashboardPayload($actor, $dashboard, $widgetRecords, $scopeSignature, $sourceSignature);

        $snapshot = DashboardSnapshot::query()->create([
            'company_id' => $actor->company_id,
            'dashboard_key' => $dashboard['key'],
            'scope_signature' => $scopeSignature,
            'source_signature' => $sourceSignature,
            'freshness_expectation_minutes' => $generatedPayload['freshness']['expectation_minutes'],
            'generated_at' => $generatedPayload['freshness']['generated_at'],
            'expires_at' => $generatedPayload['freshness']['expires_at'],
            'payload' => $generatedPayload,
        ]);

        $this->auditLogger->record(
            'reporting.dashboard.snapshot.generated',
            $actor,
            [
                'dashboard_key' => $dashboard['key'],
                'snapshot_id' => $snapshot->id,
                'scope_signature' => $scopeSignature,
                'source_signature' => $sourceSignature,
                'widget_count' => count($generatedPayload['widgets']),
                'freshness_expectation_minutes' => $snapshot->freshness_expectation_minutes,
            ],
            entityType: 'dashboard_snapshot',
            entityId: (string) $snapshot->id,
        );

        $payload = $this->payloadFromSnapshot($snapshot, false);
        $this->recordDashboardViewAudit($actor, $dashboard['key'], $snapshot, false, count($payload['widgets']));

        return $payload;
    }

    /**
     * @return DashboardDefinition
     */
    private function resolveAccessibleDashboard(User $actor, string $dashboardKey): array
    {
        $dashboard = collect($this->dashboardDefinitions())->firstWhere('key', $dashboardKey);

        if (! is_array($dashboard)) {
            throw new NotFoundHttpException('The selected reporting dashboard is not registered.');
        }

        $canView = match ($dashboardKey) {
            'hr_overview' => $actor->canAny(['reporting.manage', 'reporting.certify'])
                || ($actor->can('reporting.view') && $actor->canAny(['employee.manage', 'organization.manage'])),
            'manager_overview' => $actor->can('reporting.view')
                && $actor->canAny(['attendance.approve', 'leave.approve', 'performance.review']),
            'payroll_overview' => $actor->can('reporting.view')
                && $actor->canAny(['payroll.view', 'payroll.process', 'payroll.approve', 'compensation.view']),
            'recruiter_overview' => $actor->can('reporting.view')
                && $actor->canAny(['recruitment.view', 'recruitment.manage', 'recruitment.approve']),
            'leadership_overview' => $actor->canAny(['reporting.manage', 'reporting.certify']),
            default => false,
        };

        if (! $canView) {
            throw new AuthorizationException('You are not allowed to view this reporting dashboard.');
        }

        return $dashboard;
    }

    /**
     * @param  DashboardDefinition  $dashboard
     * @return Collection<int, DashboardWidget>
     */
    private function syncDashboardWidgets(User $actor, array $dashboard): Collection
    {
        $kpiDefinitions = KpiDefinition::query()
            ->where('company_id', $actor->company_id)
            ->whereIn('key', collect($dashboard['widgets'])->pluck('kpi_key'))
            ->get()
            ->keyBy('key');
        $datasets = ReportDataset::query()
            ->where('company_id', $actor->company_id)
            ->whereIn('key', collect($dashboard['widgets'])->pluck('dataset_key'))
            ->get()
            ->keyBy('key');

        foreach ($dashboard['widgets'] as $widgetDefinition) {
            /** @var KpiDefinition|null $kpiDefinition */
            $kpiDefinition = $kpiDefinitions->get($widgetDefinition['kpi_key']);
            /** @var ReportDataset|null $dataset */
            $dataset = $datasets->get($widgetDefinition['dataset_key']);

            DashboardWidget::query()->updateOrCreate(
                [
                    'company_id' => $actor->company_id,
                    'dashboard_key' => $dashboard['key'],
                    'widget_key' => $widgetDefinition['key'],
                ],
                [
                    'name' => $widgetDefinition['name'],
                    'widget_type' => $widgetDefinition['widget_type'],
                    'description' => $widgetDefinition['description'],
                    'position' => $widgetDefinition['position'],
                    'kpi_definition_id' => $kpiDefinition?->id,
                    'report_dataset_id' => $dataset?->id,
                    'configuration' => [
                        'kpi_key' => $widgetDefinition['kpi_key'],
                        'dataset_key' => $widgetDefinition['dataset_key'],
                        'aggregate_key' => $widgetDefinition['aggregate_key'],
                        'default_drilldown_path' => $widgetDefinition['default_drilldown_path'],
                    ],
                    'freshness_expectation_minutes' => $widgetDefinition['freshness_expectation_minutes'],
                    'is_active' => true,
                ],
            );
        }

        return DashboardWidget::query()
            ->with(['kpiDefinition', 'reportDataset'])
            ->where('company_id', $actor->company_id)
            ->where('dashboard_key', $dashboard['key'])
            ->where('is_active', true)
            ->orderBy('position')
            ->get();
    }

    /**
     * @param  DashboardDefinition  $dashboard
     * @param  Collection<int, DashboardWidget>  $widgetRecords
     * @return array{
     *   dashboard: array<string, mixed>,
     *   snapshot: array<string, mixed>,
     *   freshness: array<string, mixed>,
     *   widgets: list<array<string, mixed>>
     * }
     */
    private function buildDashboardPayload(User $actor, array $dashboard, Collection $widgetRecords, string $scopeSignature, string $sourceSignature): array
    {
        $generatedAt = now();
        $widgets = $widgetRecords->map(fn (DashboardWidget $widget): array => $this->buildWidgetPayload($actor, $widget, $generatedAt))
            ->values()
            ->all();
        $freshnessExpectationMinutes = (int) max(
            1,
            $widgetRecords->map(fn (DashboardWidget $widget): int => $this->resolveWidgetFreshnessMinutes($widget))
                ->min() ?? 60,
        );
        $expiresAt = (clone $generatedAt)->addMinutes($freshnessExpectationMinutes);

        return [
            'dashboard' => [
                'key' => $dashboard['key'],
                'name' => $dashboard['name'],
                'persona' => $dashboard['persona'],
                'description' => $dashboard['description'],
            ],
            'snapshot' => [
                'id' => null,
                'cache_hit' => false,
                'generated_at' => $generatedAt->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
                'scope_signature' => $scopeSignature,
                'source_signature' => $sourceSignature,
            ],
            'freshness' => [
                'generated_at' => $generatedAt->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
                'expectation_minutes' => $freshnessExpectationMinutes,
                'is_stale' => false,
            ],
            'widgets' => $widgets,
        ];
    }

    /**
     * @return array{
     *   dashboard: array<string, mixed>,
     *   snapshot: array<string, mixed>,
     *   freshness: array<string, mixed>,
     *   widgets: list<array<string, mixed>>
     * }
     */
    private function payloadFromSnapshot(DashboardSnapshot $snapshot, bool $cacheHit): array
    {
        $payload = $snapshot->payload;
        $payload['snapshot']['id'] = $snapshot->id;
        $payload['snapshot']['cache_hit'] = $cacheHit;
        $payload['snapshot']['generated_at'] = $snapshot->generated_at->toIso8601String();
        $payload['snapshot']['expires_at'] = $snapshot->expires_at->toIso8601String();
        $payload['freshness']['generated_at'] = $snapshot->generated_at->toIso8601String();
        $payload['freshness']['expires_at'] = $snapshot->expires_at->toIso8601String();
        $payload['freshness']['expectation_minutes'] = $snapshot->freshness_expectation_minutes;
        $payload['freshness']['is_stale'] = $snapshot->expires_at->isPast();

        return $payload;
    }

    private function buildWidgetPayload(User $actor, DashboardWidget $widget, Carbon $generatedAt): array
    {
        $dataset = $widget->reportDataset;
        $kpiDefinition = $widget->kpiDefinition;
        $blockedReason = $this->widgetBlockedReason($actor, $widget);
        $freshnessExpectationMinutes = $this->resolveWidgetFreshnessMinutes($widget);
        $expiresAt = (clone $generatedAt)->addMinutes($freshnessExpectationMinutes);
        $drilldown = $this->resolveWidgetDrilldown($actor, $widget);

        if ($blockedReason !== null || ! $dataset || ! $kpiDefinition) {
            return [
                'key' => $widget->widget_key,
                'name' => $widget->name,
                'widget_type' => $widget->widget_type,
                'description' => $widget->description,
                'status' => 'blocked',
                'blocked_reason' => $blockedReason ?? 'widget_backing_missing',
                'value' => null,
                'unit' => 'count',
                'drilldown' => null,
                'governance' => $this->widgetGovernancePayload($widget),
                'freshness' => [
                    'generated_at' => $generatedAt->toIso8601String(),
                    'expires_at' => $expiresAt->toIso8601String(),
                    'expectation_minutes' => $freshnessExpectationMinutes,
                    'is_stale' => false,
                ],
            ];
        }

        return [
            'key' => $widget->widget_key,
            'name' => $widget->name,
            'widget_type' => $widget->widget_type,
            'description' => $widget->description,
            'status' => 'ready',
            'blocked_reason' => null,
            'value' => $this->aggregateWidgetValue($actor, $widget),
            'unit' => 'count',
            'drilldown' => $drilldown,
            'governance' => $this->widgetGovernancePayload($widget),
            'freshness' => [
                'generated_at' => $generatedAt->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
                'expectation_minutes' => $freshnessExpectationMinutes,
                'is_stale' => false,
            ],
        ];
    }

    private function widgetBlockedReason(User $actor, DashboardWidget $widget): ?string
    {
        $kpiDefinition = $widget->kpiDefinition;
        $dataset = $widget->reportDataset;

        if (! $kpiDefinition) {
            return 'kpi_definition_missing';
        }

        if (! $dataset) {
            return 'report_dataset_missing';
        }

        if ($kpiDefinition->certification_status !== 'certified' && ! $actor->canAny(['reporting.manage', 'reporting.certify'])) {
            return 'kpi_not_certified';
        }

        if ($dataset->certification_status !== 'certified' && ! $actor->canAny(['reporting.manage', 'reporting.certify'])) {
            return 'dataset_not_certified';
        }

        if (! $this->accessScopeService->canViewDatasetDomain($actor, $dataset->domain)) {
            return 'dataset_domain_not_allowed';
        }

        return null;
    }

    private function resolveWidgetFreshnessMinutes(DashboardWidget $widget): int
    {
        $widgetFreshness = $widget->freshness_expectation_minutes ?? 60;
        $datasetFreshness = $widget->reportDataset?->freshness_expectation_minutes;

        if (! is_int($datasetFreshness) || $datasetFreshness <= 0) {
            return max(1, (int) $widgetFreshness);
        }

        return max(1, min((int) $widgetFreshness, $datasetFreshness));
    }

    private function aggregateWidgetValue(User $actor, DashboardWidget $widget): int
    {
        /** @var array<string, mixed> $configuration */
        $configuration = $widget->configuration ?? [];

        return match ($configuration['aggregate_key'] ?? null) {
            'active_headcount' => $this->countActiveEmployees($actor),
            'attendance_exceptions_today' => $this->countAttendanceExceptionsToday($actor),
            'pending_leave_requests' => $this->countPendingLeaveRequests($actor),
            'active_candidates' => $this->countActiveCandidates($actor),
            'interview_stage_candidates' => $this->countCandidatesByStage($actor, 'interview'),
            'offer_stage_candidates' => $this->countCandidatesByStage($actor, 'offer'),
            'payroll_runs_in_progress' => $this->countPayrollRunsByStatus($actor, ['ready', 'calculated', 'approved']),
            'payroll_runs_locked' => $this->countPayrollRunsByStatus($actor, ['locked']),
            'payroll_runs_blocked' => $this->countPayrollRunsByStatus($actor, ['blocked', 'failed']),
            'open_performance_reviews' => $this->countOpenPerformanceReviews($actor),
            'overdue_learning_assignments' => $this->countOverdueLearningAssignments($actor),
            default => 0,
        };
    }

    private function countActiveEmployees(User $actor): int
    {
        $query = Employee::query()
            ->where('company_id', $actor->company_id)
            ->where('employment_status', 'active');

        return $this->applyEmployeeScope($query, $this->accessScopeService->workforceEmployeeIds($actor))->count();
    }

    private function countAttendanceExceptionsToday(User $actor): int
    {
        $query = DB::table('attendance_records')
            ->where('company_id', $actor->company_id)
            ->whereDate('attendance_date', now()->toDateString())
            ->where(function (QueryBuilder $builder): void {
                $builder->whereNotIn('primary_status', ['present', 'holiday', 'weekend', 'on_leave'])
                    ->orWhere('is_late', true);
            });

        return $this->applyEmployeeScope($query, $this->accessScopeService->attendanceEmployeeIds($actor), 'employee_id')->count();
    }

    private function countPendingLeaveRequests(User $actor): int
    {
        $query = LeaveRequest::query()
            ->where('company_id', $actor->company_id)
            ->where('status', 'pending');

        return $this->applyEmployeeScope($query, $this->accessScopeService->leaveEmployeeIds($actor), 'employee_id')->count();
    }

    private function countActiveCandidates(User $actor): int
    {
        $query = DB::table('candidates')
            ->leftJoin('job_requisitions', 'job_requisitions.id', '=', 'candidates.job_requisition_id')
            ->where('candidates.company_id', $actor->company_id)
            ->where('candidates.status', 'active');

        $this->applyRecruitmentScope($query, $actor);

        return $query->count('candidates.id');
    }

    private function countCandidatesByStage(User $actor, string $stage): int
    {
        $query = DB::table('candidates')
            ->leftJoin('job_requisitions', 'job_requisitions.id', '=', 'candidates.job_requisition_id')
            ->where('candidates.company_id', $actor->company_id)
            ->where('candidates.status', 'active')
            ->where('candidates.current_stage', $stage);

        $this->applyRecruitmentScope($query, $actor);

        return $query->count('candidates.id');
    }

    private function countPayrollRunsByStatus(User $actor, array $statuses): int
    {
        return (int) DB::table('payroll_runs')
            ->where('company_id', $actor->company_id)
            ->whereIn('status', $statuses)
            ->count();
    }

    private function countOpenPerformanceReviews(User $actor): int
    {
        $query = DB::table('performance_reviews')
            ->where('company_id', $actor->company_id)
            ->where('status', '!=', 'published');

        if (! $actor->canAny(['performance.manage', 'performance.calibrate'])) {
            $actorEmployeeId = $this->accessScopeService->linkedEmployeeId($actor);

            $query->where(function (QueryBuilder $builder) use ($actor, $actorEmployeeId): void {
                if ($actorEmployeeId !== null) {
                    $builder->orWhere('performance_reviews.employee_id', $actorEmployeeId)
                        ->orWhere('performance_reviews.manager_employee_id', $actorEmployeeId);
                }

                $builder->orWhereJsonContains('performance_reviews.reviewer_user_ids', $actor->id);
            });
        }

        return (int) $query->count('performance_reviews.id');
    }

    private function countOverdueLearningAssignments(User $actor): int
    {
        $query = LearningAssignmentTarget::query()
            ->where('company_id', $actor->company_id)
            ->whereDate('due_on', '<', now()->toDateString())
            ->where('status', '!=', 'completed');

        return $this->applyEmployeeScope($query, $this->accessScopeService->learningEmployeeIds($actor), 'employee_id')->count();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveWidgetDrilldown(User $actor, DashboardWidget $widget): ?array
    {
        $dataset = $widget->reportDataset;

        if (! $dataset) {
            return null;
        }

        /** @var array<string, mixed> $configuration */
        $configuration = $widget->configuration ?? [];
        $defaultPathKey = $configuration['default_drilldown_path'] ?? null;

        if (! is_string($defaultPathKey) || $defaultPathKey === '') {
            return null;
        }

        $path = collect($dataset->drilldown_paths ?? [])
            ->first(fn (mixed $candidate): bool => is_array($candidate) && ($candidate['key'] ?? null) === $defaultPathKey);

        if (! is_array($path)) {
            return null;
        }

        $targetDatasets = $this->accessScopeService->resolveTargetDatasets($actor, [$path['target_dataset_key'] ?? null]);

        if (! $this->accessScopeService->canAccessDrilldownPath($actor, $dataset, $path, $targetDatasets)) {
            return null;
        }

        return [
            'key' => $path['key'],
            'label' => $path['label'] ?? $path['key'],
            'target_dataset_key' => $path['target_dataset_key'] ?? null,
            'description' => $path['description'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function widgetGovernancePayload(DashboardWidget $widget): array
    {
        /** @var array<string, mixed> $configuration */
        $configuration = $widget->configuration ?? [];

        return [
            'kpi' => [
                'key' => $widget->kpiDefinition?->key ?? $configuration['kpi_key'] ?? null,
                'name' => $widget->kpiDefinition?->name,
                'formula' => $widget->kpiDefinition?->formula,
                'version' => $widget->kpiDefinition?->version,
                'certification_status' => $widget->kpiDefinition?->certification_status,
                'source_references' => $widget->kpiDefinition?->source_references ?? [],
            ],
            'dataset' => [
                'key' => $widget->reportDataset?->key ?? $configuration['dataset_key'] ?? null,
                'name' => $widget->reportDataset?->name,
                'domain' => $widget->reportDataset?->domain,
                'version' => $widget->reportDataset?->version,
                'certification_status' => $widget->reportDataset?->certification_status,
                'freshness_expectation_minutes' => $widget->reportDataset?->freshness_expectation_minutes,
                'masking_posture' => $widget->reportDataset?->masking_posture ?? [],
            ],
        ];
    }

    /**
     * @param  Collection<int, DashboardWidget>  $widgetRecords
     */
    private function sourceSignature(Collection $widgetRecords): string
    {
        return sha1((string) $widgetRecords
            ->map(function (DashboardWidget $widget): string {
                /** @var array<string, mixed> $configuration */
                $configuration = $widget->configuration ?? [];

                return implode('|', [
                    $widget->widget_key,
                    $widget->kpiDefinition?->key ?? (string) ($configuration['kpi_key'] ?? 'missing'),
                    (string) ($widget->kpiDefinition?->version ?? 0),
                    $widget->reportDataset?->key ?? (string) ($configuration['dataset_key'] ?? 'missing'),
                    (string) ($widget->reportDataset?->version ?? 0),
                    (string) ($widget->freshness_expectation_minutes ?? 0),
                ]);
            })
            ->implode('||'));
    }

    private function scopeSignature(User $actor, string $dashboardKey): string
    {
        $linkedEmployeeId = $this->accessScopeService->linkedEmployeeId($actor) ?? 0;

        return sha1(implode('|', [
            $dashboardKey,
            (string) $actor->company_id,
            (string) $actor->id,
            (string) $linkedEmployeeId,
            collect($actor->getAllPermissions())->pluck('name')->sort()->implode(','),
        ]));
    }

    private function recordDashboardViewAudit(User $actor, string $dashboardKey, DashboardSnapshot $snapshot, bool $cacheHit, int $widgetCount): void
    {
        $this->auditLogger->record(
            'reporting.dashboard.viewed',
            $actor,
            [
                'dashboard_key' => $dashboardKey,
                'snapshot_id' => $snapshot->id,
                'cache_hit' => $cacheHit,
                'widget_count' => $widgetCount,
                'generated_at' => $snapshot->generated_at->toIso8601String(),
                'expires_at' => $snapshot->expires_at->toIso8601String(),
                'freshness_expectation_minutes' => $snapshot->freshness_expectation_minutes,
            ],
            entityType: 'dashboard_snapshot',
            entityId: (string) $snapshot->id,
        );
    }

    /**
     * @return EloquentBuilder<Employee>|QueryBuilder
     */
    private function applyEmployeeScope(EloquentBuilder|QueryBuilder $query, ?array $employeeIds, string $column = 'id'): EloquentBuilder|QueryBuilder
    {
        if (! is_array($employeeIds)) {
            return $query;
        }

        if ($employeeIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $employeeIds);
    }

    private function applyRecruitmentScope(QueryBuilder $query, User $actor): void
    {
        if ($actor->can('recruitment.manage')) {
            return;
        }

        $actorEmployeeId = $this->accessScopeService->linkedEmployeeId($actor);

        $query->where(function (QueryBuilder $builder) use ($actor, $actorEmployeeId): void {
            $builder->where('candidates.recruiter_user_id', $actor->id)
                ->orWhere('job_requisitions.recruiter_user_id', $actor->id);

            if ($actorEmployeeId !== null) {
                $builder->orWhere('job_requisitions.hiring_manager_employee_id', $actorEmployeeId);
            }
        });
    }

    /**
     * @return list<DashboardDefinition>
     */
    private function dashboardDefinitions(): array
    {
        return [
            [
                'key' => 'hr_overview',
                'name' => 'HR overview',
                'persona' => 'hr',
                'description' => 'Operational HR dashboard for workforce, attendance, leave, and recruitment posture.',
                'widgets' => [
                    $this->metricWidget('active_headcount_card', 'Active headcount', 'active_headcount', 'workforce_headcount_snapshot', 'active_headcount', 1, 'employee_profile', 1440, 'Current active workforce count sourced from the governed workforce snapshot.'),
                    $this->metricWidget('attendance_exceptions_card', 'Attendance exceptions today', 'attendance_exceptions_today', 'attendance_daily_register', 'attendance_exceptions_today', 2, null, 60, 'Today\'s attendance exceptions from the governed attendance register.'),
                    $this->metricWidget('pending_leave_requests_card', 'Pending leave requests', 'pending_leave_requests', 'leave_request_register', 'pending_leave_requests', 3, 'leave_request_detail', 120, 'Open leave approvals sourced from the governed leave request register.'),
                    $this->metricWidget('active_candidates_card', 'Active candidates', 'active_candidates', 'recruitment_candidate_pipeline', 'active_candidates', 4, null, 180, 'Active recruitment pipeline volume sourced from the governed candidate pipeline.'),
                ],
            ],
            [
                'key' => 'manager_overview',
                'name' => 'Manager overview',
                'persona' => 'manager',
                'description' => 'Team-scoped dashboard for people managers across headcount, attendance, leave, and reviews.',
                'widgets' => [
                    $this->metricWidget('team_headcount_card', 'Team headcount', 'active_headcount', 'workforce_headcount_snapshot', 'active_headcount', 1, 'employee_profile', 1440, 'Active employees within the manager-visible workforce scope.'),
                    $this->metricWidget('team_attendance_exceptions_card', 'Team attendance exceptions today', 'attendance_exceptions_today', 'attendance_daily_register', 'attendance_exceptions_today', 2, null, 60, 'Today\'s attendance exceptions within the manager-visible attendance scope.'),
                    $this->metricWidget('team_pending_leave_requests_card', 'Pending team leave requests', 'pending_leave_requests', 'leave_request_register', 'pending_leave_requests', 3, 'leave_request_detail', 120, 'Pending leave approvals within the manager-visible leave scope.'),
                    $this->metricWidget('open_team_reviews_card', 'Open performance reviews', 'open_performance_reviews', 'performance_review_status', 'open_performance_reviews', 4, null, 240, 'Performance reviews still in progress inside the manager-visible review scope.'),
                ],
            ],
            [
                'key' => 'payroll_overview',
                'name' => 'Payroll overview',
                'persona' => 'payroll',
                'description' => 'Run-state dashboard for payroll operations and close readiness.',
                'widgets' => [
                    $this->metricWidget('payroll_runs_in_progress_card', 'Runs in progress', 'active_payroll_runs', 'payroll_run_register', 'payroll_runs_in_progress', 1, null, 240, 'Payroll runs that are currently moving through preparation, calculation, or approval.'),
                    $this->metricWidget('payroll_runs_locked_card', 'Locked payroll runs', 'locked_payroll_runs', 'payroll_run_register', 'payroll_runs_locked', 2, null, 240, 'Payroll runs that are locked and ready for downstream release controls.'),
                    $this->metricWidget('payroll_runs_blocked_card', 'Blocked payroll runs', 'blocked_payroll_runs', 'payroll_run_register', 'payroll_runs_blocked', 3, null, 120, 'Payroll runs currently blocked or failed and needing intervention.'),
                ],
            ],
            [
                'key' => 'recruiter_overview',
                'name' => 'Recruiter overview',
                'persona' => 'recruiter',
                'description' => 'Recruitment pipeline dashboard for active sourcing, interview, and offer movement.',
                'widgets' => [
                    $this->metricWidget('recruiter_active_candidates_card', 'Active candidates', 'active_candidates', 'recruitment_candidate_pipeline', 'active_candidates', 1, null, 180, 'Candidates still active in the recruiter-visible hiring pipeline.'),
                    $this->metricWidget('interview_stage_candidates_card', 'Interview stage candidates', 'interview_stage_candidates', 'recruitment_candidate_pipeline', 'interview_stage_candidates', 2, null, 180, 'Candidates currently in the interview stage of the recruiter-visible pipeline.'),
                    $this->metricWidget('offer_stage_candidates_card', 'Offer stage candidates', 'offer_stage_candidates', 'recruitment_candidate_pipeline', 'offer_stage_candidates', 3, null, 180, 'Candidates currently at the offer stage of the recruiter-visible pipeline.'),
                ],
            ],
            [
                'key' => 'leadership_overview',
                'name' => 'Leadership overview',
                'persona' => 'leadership',
                'description' => 'Executive operating dashboard across workforce, recruitment, learning, and performance.',
                'widgets' => [
                    $this->metricWidget('leadership_headcount_card', 'Enterprise active headcount', 'active_headcount', 'workforce_headcount_snapshot', 'active_headcount', 1, 'employee_profile', 1440, 'Company-wide active headcount sourced from the governed workforce snapshot.'),
                    $this->metricWidget('leadership_active_candidates_card', 'Enterprise active candidates', 'active_candidates', 'recruitment_candidate_pipeline', 'active_candidates', 2, null, 180, 'Company-wide active recruitment pipeline volume.'),
                    $this->metricWidget('leadership_open_reviews_card', 'Open performance reviews', 'open_performance_reviews', 'performance_review_status', 'open_performance_reviews', 3, null, 240, 'Company-wide performance reviews still in progress.'),
                    $this->metricWidget('leadership_learning_overdue_card', 'Overdue learning assignments', 'overdue_learning_assignments', 'learning_assignment_targets', 'overdue_learning_assignments', 4, null, 240, 'Mandatory learning items that are past due and still incomplete.'),
                ],
            ],
        ];
    }

    /**
     * @return DashboardWidgetDefinition
     */
    private function metricWidget(
        string $key,
        string $name,
        string $kpiKey,
        string $datasetKey,
        string $aggregateKey,
        int $position,
        ?string $defaultDrilldownPath,
        int $freshnessExpectationMinutes,
        string $description,
    ): array {
        return [
            'key' => $key,
            'name' => $name,
            'widget_type' => 'metric',
            'description' => $description,
            'position' => $position,
            'kpi_key' => $kpiKey,
            'dataset_key' => $datasetKey,
            'aggregate_key' => $aggregateKey,
            'default_drilldown_path' => $defaultDrilldownPath,
            'freshness_expectation_minutes' => $freshnessExpectationMinutes,
        ];
    }
}
