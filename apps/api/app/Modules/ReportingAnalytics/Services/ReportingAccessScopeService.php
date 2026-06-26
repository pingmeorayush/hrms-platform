<?php

namespace App\Modules\ReportingAnalytics\Services;

use App\Models\Employee;
use App\Models\ReportDataset;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;

class ReportingAccessScopeService
{
    public function resolveAccessibleDataset(User $actor, string $datasetKey): ReportDataset
    {
        $dataset = ReportDataset::query()
            ->where('company_id', $actor->company_id)
            ->where('key', $datasetKey)
            ->firstOrFail();

        if (! $this->canViewDatasetDomain($actor, $dataset->domain)) {
            throw new AuthorizationException('You are not allowed to view this reporting dataset.');
        }

        if ($dataset->certification_status !== 'certified' && ! $actor->canAny(['reporting.manage', 'reporting.certify'])) {
            throw new AuthorizationException('This reporting dataset is not certified for general consumption yet.');
        }

        return $dataset;
    }

    /**
     * @param  array{key?: mixed, sensitive?: mixed, masking_strategy?: mixed}  $fieldDefinition
     * @return array{key: string, exposure: 'full'|'masked'|'hidden', strategy: 'redact'|'partial'|'aggregate_only'|null}
     */
    public function resolveFieldExposure(User $actor, ReportDataset $dataset, array $fieldDefinition): array
    {
        $fieldKey = is_string($fieldDefinition['key'] ?? null) ? $fieldDefinition['key'] : '';
        $sensitiveFieldKeys = collect($dataset->masking_posture['sensitive_field_keys'] ?? [])
            ->filter(fn (mixed $value): bool => is_string($value))
            ->values()
            ->all();
        $isSensitive = (bool) ($fieldDefinition['sensitive'] ?? false) || in_array($fieldKey, $sensitiveFieldKeys, true);
        $strategy = $this->resolveMaskingStrategy($dataset, $fieldDefinition);

        if (! $isSensitive || $strategy === null) {
            return [
                'key' => $fieldKey,
                'exposure' => 'full',
                'strategy' => null,
            ];
        }

        if ($this->hasElevatedReportingVisibility($actor, $dataset->domain)) {
            return [
                'key' => $fieldKey,
                'exposure' => 'full',
                'strategy' => null,
            ];
        }

        return [
            'key' => $fieldKey,
            'exposure' => $strategy === 'aggregate_only' ? 'hidden' : 'masked',
            'strategy' => $strategy,
        ];
    }

    /**
     * @param  EloquentCollection<int, ReportDataset>  $targetDatasets
     * @param  array{target_dataset_key?: mixed}  $path
     */
    public function canAccessDrilldownPath(User $actor, ReportDataset $sourceDataset, array $path, EloquentCollection $targetDatasets): bool
    {
        $targetDatasetKey = $path['target_dataset_key'] ?? null;

        if (! is_string($targetDatasetKey) || $targetDatasetKey === '' || $targetDatasetKey === $sourceDataset->key) {
            return true;
        }

        /** @var ReportDataset|null $targetDataset */
        $targetDataset = $targetDatasets->firstWhere('key', $targetDatasetKey);

        if (! $targetDataset) {
            return false;
        }

        if (! $this->canViewDatasetDomain($actor, $targetDataset->domain)) {
            return false;
        }

        return $targetDataset->certification_status === 'certified'
            || $actor->canAny(['reporting.manage', 'reporting.certify']);
    }

    public function canViewDatasetDomain(User $actor, string $domain): bool
    {
        return match ($domain) {
            'workforce' => $actor->canAny(['reporting.view', 'reporting.manage', 'reporting.certify'])
                && $actor->canAny(['employee.view', 'employee.manage', 'organization.view', 'organization.manage']),
            'attendance' => $actor->canAny(['reporting.view', 'reporting.manage', 'reporting.certify'])
                && $actor->canAny(['attendance.analytics.view', 'attendance.view', 'attendance.edit', 'attendance.approve']),
            'leave' => $actor->canAny(['reporting.view', 'reporting.manage', 'reporting.certify'])
                && $actor->canAny(['leave.view', 'leave.approve', 'leave.manage_balance', 'leave.manage_policy']),
            'payroll' => $actor->canAny(['reporting.view', 'reporting.manage', 'reporting.certify'])
                && $actor->canAny(['payroll.view', 'compensation.view', 'payroll.process', 'payroll.approve']),
            'recruitment' => $actor->canAny(['reporting.view', 'reporting.manage', 'reporting.certify'])
                && $actor->canAny(['recruitment.view', 'recruitment.manage', 'recruitment.approve', 'recruitment.interview']),
            'performance' => $actor->canAny(['reporting.view', 'reporting.manage', 'reporting.certify'])
                && $actor->canAny(['performance.view', 'performance.review', 'performance.manage', 'performance.calibrate']),
            'learning' => $actor->canAny(['reporting.view', 'reporting.manage', 'reporting.certify'])
                && $actor->canAny(['learning.view', 'learning.complete', 'learning.assign', 'learning.manage']),
            'operations' => $actor->canAny(['reporting.view', 'reporting.manage', 'reporting.certify'])
                && $actor->canAny(['document.view', 'asset.view', 'employee.view', 'employee.manage']),
            'cross_domain' => $actor->canAny(['reporting.manage', 'reporting.certify']),
            default => false,
        };
    }

    /**
     * @return list<int>|null
     */
    public function workforceEmployeeIds(User $actor): ?array
    {
        if ($actor->canAny(['employee.manage', 'organization.manage', 'reporting.manage', 'reporting.certify'])) {
            return null;
        }

        $linkedEmployee = $this->findLinkedEmployee($actor);

        if (! $linkedEmployee) {
            return [];
        }

        if ($actor->hasRole('manager') || $actor->can('employee.view')) {
            return $this->selfAndDirectReportIds($actor, $linkedEmployee->id);
        }

        return [$linkedEmployee->id];
    }

    /**
     * @return list<int>|null
     */
    public function attendanceEmployeeIds(User $actor): ?array
    {
        if ($actor->canAny(['attendance.edit', 'attendance.manage_shift', 'attendance.manage_roster', 'reporting.manage', 'reporting.certify'])) {
            return null;
        }

        $linkedEmployee = $this->findLinkedEmployee($actor);

        if (! $linkedEmployee) {
            return [];
        }

        if ($actor->can('attendance.approve')) {
            return $this->selfAndDirectReportIds($actor, $linkedEmployee->id);
        }

        return [$linkedEmployee->id];
    }

    /**
     * @return list<int>|null
     */
    public function leaveEmployeeIds(User $actor): ?array
    {
        if ($actor->canAny(['leave.manage_balance', 'leave.manage_policy', 'employee.manage', 'reporting.manage', 'reporting.certify'])) {
            return null;
        }

        $linkedEmployee = $this->findLinkedEmployee($actor);

        if (! $linkedEmployee) {
            return [];
        }

        if ($actor->can('leave.approve')) {
            return $this->selfAndDirectReportIds($actor, $linkedEmployee->id);
        }

        return [$linkedEmployee->id];
    }

    /**
     * @return list<int>|null
     */
    public function learningEmployeeIds(User $actor): ?array
    {
        if ($actor->canAny(['learning.manage', 'learning.assign', 'reporting.manage', 'reporting.certify'])) {
            return null;
        }

        $linkedEmployee = $this->findLinkedEmployee($actor);

        if (! $linkedEmployee) {
            return [];
        }

        if ($actor->hasRole('manager')) {
            return $this->selfAndDirectReportIds($actor, $linkedEmployee->id);
        }

        return [$linkedEmployee->id];
    }

    /**
     * @return list<int>|null
     */
    public function performanceEmployeeIds(User $actor): ?array
    {
        if ($actor->canAny(['performance.manage', 'performance.calibrate', 'reporting.manage', 'reporting.certify'])) {
            return null;
        }

        $linkedEmployee = $this->findLinkedEmployee($actor);

        if (! $linkedEmployee) {
            return [];
        }

        if ($actor->hasRole('manager') || $actor->can('performance.review')) {
            return $this->selfAndDirectReportIds($actor, $linkedEmployee->id);
        }

        return [$linkedEmployee->id];
    }

    public function linkedEmployeeId(User $actor): ?int
    {
        return $this->findLinkedEmployee($actor)?->id;
    }

    public function nowIsoString(): string
    {
        return Carbon::now()->toIso8601String();
    }

    /**
     * @return EloquentCollection<int, ReportDataset>
     */
    public function resolveTargetDatasets(User $actor, array $datasetKeys): EloquentCollection
    {
        $normalizedKeys = collect($datasetKeys)
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->unique()
            ->values()
            ->all();

        if ($normalizedKeys === []) {
            return new EloquentCollection;
        }

        return ReportDataset::query()
            ->where('company_id', $actor->company_id)
            ->whereIn('key', $normalizedKeys)
            ->get();
    }

    private function findLinkedEmployee(User $actor): ?Employee
    {
        return Employee::query()->where('user_id', $actor->id)->first();
    }

    /**
     * @param  array{masking_strategy?: mixed}  $fieldDefinition
     */
    private function resolveMaskingStrategy(ReportDataset $dataset, array $fieldDefinition): ?string
    {
        $fieldStrategy = $fieldDefinition['masking_strategy'] ?? null;

        if (is_string($fieldStrategy) && in_array($fieldStrategy, ['redact', 'partial', 'aggregate_only'], true)) {
            return $fieldStrategy;
        }

        $defaultStrategy = $dataset->masking_posture['default_strategy'] ?? null;

        return is_string($defaultStrategy) && in_array($defaultStrategy, ['redact', 'partial', 'aggregate_only'], true)
            ? $defaultStrategy
            : null;
    }

    private function hasElevatedReportingVisibility(User $actor, string $domain): bool
    {
        if ($actor->canAny(['reporting.manage', 'reporting.certify'])) {
            return true;
        }

        return match ($domain) {
            'workforce' => $actor->canAny(['employee.manage', 'organization.manage']),
            'attendance' => $actor->canAny(['attendance.edit', 'attendance.approve', 'attendance.manage_shift', 'attendance.manage_roster']),
            'leave' => $actor->canAny(['leave.manage_balance', 'leave.manage_policy', 'employee.manage']),
            'payroll' => $actor->canAny(['payroll.process', 'payroll.approve', 'compensation.view']),
            'recruitment' => $actor->canAny(['recruitment.manage', 'recruitment.approve']),
            'performance' => $actor->canAny(['performance.manage', 'performance.calibrate']),
            'learning' => $actor->canAny(['learning.manage', 'learning.assign']),
            'operations' => $actor->canAny(['document.manage', 'asset.manage', 'employee.manage']),
            'cross_domain' => false,
            default => false,
        };
    }

    /**
     * @return list<int>
     */
    private function selfAndDirectReportIds(User $actor, int $linkedEmployeeId): array
    {
        return collect([$linkedEmployeeId])
            ->merge(
                Employee::query()
                    ->where('company_id', $actor->company_id)
                    ->where('manager_id', $linkedEmployeeId)
                    ->pluck('id'),
            )
            ->unique()
            ->values()
            ->all();
    }
}
