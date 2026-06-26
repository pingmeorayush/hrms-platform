<?php

namespace App\Modules\ReportingAnalytics\Services;

use App\Models\KpiDefinition;
use App\Models\ReportDataset;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type ReportingCatalogFilters array{
 *   domain?: string,
 *   certification_status?: string,
 *   owner_user_id?: int|string,
 *   grain?: string,
 *   q?: string,
 *   per_page?: int|string
 * }
 * @phpstan-type SourceReference array{
 *   module: string,
 *   entity: string,
 *   field: string|null,
 *   notes: string|null
 * }
 * @phpstan-type ApprovedField array{
 *   key: string,
 *   label: string,
 *   type: string,
 *   description: string|null,
 *   sensitive: bool,
 *   masking_strategy: string|null
 * }
 * @phpstan-type ApprovedFilter array{
 *   key: string,
 *   label: string,
 *   type: string,
 *   required: bool,
 *   operators: list<string>
 * }
 * @phpstan-type DrilldownPath array{
 *   key: string,
 *   label: string,
 *   target_dataset_key: string|null,
 *   description: string|null,
 *   allowed_filter_keys: list<string>
 * }
 * @phpstan-type MaskingPosture array{
 *   default_strategy: string,
 *   sensitive_field_keys: list<string>,
 *   notes: string|null
 * }
 * @phpstan-type KpiPayload array{
 *   key?: string,
 *   name?: string,
 *   domain?: string,
 *   description?: string|null,
 *   formula?: string,
 *   source_references?: list<array<string, mixed>>,
 *   grain?: string,
 *   certification_status?: string,
 *   review_notes?: string|null,
 *   owner_user_id?: int|string|null
 * }
 * @phpstan-type DatasetPayload array{
 *   key?: string,
 *   name?: string,
 *   domain?: string,
 *   description?: string|null,
 *   source_references?: list<array<string, mixed>>,
 *   grain?: string,
 *   approved_fields?: list<array<string, mixed>>,
 *   approved_filters?: list<array<string, mixed>>|null,
 *   drilldown_paths?: list<array<string, mixed>>|null,
 *   masking_posture?: array<string, mixed>,
 *   freshness_expectation_minutes?: int|string|null,
 *   certification_status?: string,
 *   review_notes?: string|null,
 *   owner_user_id?: int|string|null
 * }
 */
class ReportingCatalogService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  ReportingCatalogFilters  $filters
     */
    public function searchKpis(User $actor, array $filters): LengthAwarePaginator
    {
        $query = KpiDefinition::query()
            ->with(['owner', 'reviewedBy', 'certifiedBy', 'createdBy', 'updatedBy'])
            ->orderBy('name');

        if (is_string($filters['domain'] ?? null) && $filters['domain'] !== '') {
            $query->where('domain', $filters['domain']);
        }

        if (is_string($filters['certification_status'] ?? null) && $filters['certification_status'] !== '') {
            $query->where('certification_status', $filters['certification_status']);
        }

        if (is_numeric($filters['owner_user_id'] ?? null)) {
            $query->where('owner_user_id', (int) $filters['owner_user_id']);
        }

        if (is_string($filters['q'] ?? null) && $filters['q'] !== '') {
            $needle = trim($filters['q']);
            $query->where(function (Builder $builder) use ($needle): void {
                $builder
                    ->where('key', 'like', '%'.$needle.'%')
                    ->orWhere('name', 'like', '%'.$needle.'%')
                    ->orWhere('description', 'like', '%'.$needle.'%');
            });
        }

        $perPage = is_numeric($filters['per_page'] ?? null) ? (int) $filters['per_page'] : 15;

        return $query->paginate($perPage);
    }

    /**
     * @param  ReportingCatalogFilters  $filters
     */
    public function searchDatasets(User $actor, array $filters): LengthAwarePaginator
    {
        $query = ReportDataset::query()
            ->with(['owner', 'reviewedBy', 'certifiedBy', 'createdBy', 'updatedBy'])
            ->orderBy('name');

        if (is_string($filters['domain'] ?? null) && $filters['domain'] !== '') {
            $query->where('domain', $filters['domain']);
        }

        if (is_string($filters['certification_status'] ?? null) && $filters['certification_status'] !== '') {
            $query->where('certification_status', $filters['certification_status']);
        }

        if (is_numeric($filters['owner_user_id'] ?? null)) {
            $query->where('owner_user_id', (int) $filters['owner_user_id']);
        }

        if (is_string($filters['grain'] ?? null) && $filters['grain'] !== '') {
            $query->where('grain', $filters['grain']);
        }

        if (is_string($filters['q'] ?? null) && $filters['q'] !== '') {
            $needle = trim($filters['q']);
            $query->where(function (Builder $builder) use ($needle): void {
                $builder
                    ->where('key', 'like', '%'.$needle.'%')
                    ->orWhere('name', 'like', '%'.$needle.'%')
                    ->orWhere('description', 'like', '%'.$needle.'%');
            });
        }

        $perPage = is_numeric($filters['per_page'] ?? null) ? (int) $filters['per_page'] : 15;

        return $query->paginate($perPage);
    }

    public function findKpiForView(User $actor, int $kpiDefinitionId): KpiDefinition
    {
        return KpiDefinition::query()
            ->with(['owner', 'reviewedBy', 'certifiedBy', 'createdBy', 'updatedBy'])
            ->findOrFail($kpiDefinitionId);
    }

    public function findDatasetForView(User $actor, int $reportDatasetId): ReportDataset
    {
        return ReportDataset::query()
            ->with(['owner', 'reviewedBy', 'certifiedBy', 'createdBy', 'updatedBy'])
            ->findOrFail($reportDatasetId);
    }

    /**
     * @param  KpiPayload  $payload
     */
    public function createKpi(User $actor, array $payload): KpiDefinition
    {
        return DB::transaction(function () use ($actor, $payload): KpiDefinition {
            $sourceReferences = $this->normalizeSourceReferences($payload['source_references'] ?? []);
            $governance = $this->resolveGovernanceState($actor, $payload['certification_status'] ?? 'draft', $payload['review_notes'] ?? null);

            $kpi = KpiDefinition::query()->create([
                'key' => $payload['key'],
                'name' => $payload['name'],
                'domain' => $payload['domain'],
                'description' => $this->nullableTrimmedString($payload['description'] ?? null),
                'formula' => trim((string) $payload['formula']),
                'source_references' => $sourceReferences,
                'grain' => trim((string) $payload['grain']),
                'owner_user_id' => $this->nullableInt($payload['owner_user_id'] ?? null),
                'version' => 1,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
                ...$governance,
            ]);

            $this->auditLogger->record(
                'reporting.kpi.created',
                $actor,
                [
                    'kpi_definition_id' => $kpi->id,
                    'key' => $kpi->key,
                    'domain' => $kpi->domain,
                    'certification_status' => $kpi->certification_status,
                    'version' => $kpi->version,
                ],
                entityType: 'kpi_definition',
                entityId: (string) $kpi->id,
            );

            return $this->findKpiForView($actor, $kpi->id);
        });
    }

    /**
     * @param  KpiPayload  $payload
     */
    public function updateKpi(User $actor, KpiDefinition $kpi, array $payload): KpiDefinition
    {
        return DB::transaction(function () use ($actor, $kpi, $payload): KpiDefinition {
            $updates = [];

            if (array_key_exists('name', $payload)) {
                $updates['name'] = trim((string) $payload['name']);
            }

            if (array_key_exists('domain', $payload)) {
                $updates['domain'] = $payload['domain'];
            }

            if (array_key_exists('description', $payload)) {
                $updates['description'] = $this->nullableTrimmedString($payload['description']);
            }

            if (array_key_exists('formula', $payload)) {
                $updates['formula'] = trim((string) $payload['formula']);
            }

            if (array_key_exists('source_references', $payload)) {
                $updates['source_references'] = $this->normalizeSourceReferences($payload['source_references'] ?? []);
            }

            if (array_key_exists('grain', $payload)) {
                $updates['grain'] = trim((string) $payload['grain']);
            }

            if (array_key_exists('owner_user_id', $payload)) {
                $updates['owner_user_id'] = $this->nullableInt($payload['owner_user_id']);
            }

            $governance = $this->resolveGovernanceState(
                $actor,
                $payload['certification_status'] ?? $kpi->certification_status,
                array_key_exists('review_notes', $payload) ? $payload['review_notes'] : $kpi->review_notes,
            );

            $kpi->fill([
                ...$updates,
                ...$governance,
                'version' => $kpi->version + 1,
                'updated_by_user_id' => $actor->id,
            ]);
            $kpi->save();

            $this->auditLogger->record(
                'reporting.kpi.updated',
                $actor,
                [
                    'kpi_definition_id' => $kpi->id,
                    'key' => $kpi->key,
                    'domain' => $kpi->domain,
                    'certification_status' => $kpi->certification_status,
                    'version' => $kpi->version,
                ],
                entityType: 'kpi_definition',
                entityId: (string) $kpi->id,
            );

            return $this->findKpiForView($actor, $kpi->id);
        });
    }

    /**
     * @param  DatasetPayload  $payload
     */
    public function createDataset(User $actor, array $payload): ReportDataset
    {
        return DB::transaction(function () use ($actor, $payload): ReportDataset {
            $approvedFields = $this->normalizeApprovedFields($payload['approved_fields'] ?? []);
            $approvedFilters = $this->normalizeApprovedFilters($payload['approved_filters'] ?? null);
            $drilldownPaths = $this->normalizeDrilldownPaths($payload['drilldown_paths'] ?? null);
            $maskingPosture = $this->normalizeMaskingPosture($payload['masking_posture'] ?? []);

            $this->assertDatasetStructureIsValid($approvedFields, $approvedFilters, $drilldownPaths, $maskingPosture);

            $sourceReferences = $this->normalizeSourceReferences($payload['source_references'] ?? []);
            $governance = $this->resolveGovernanceState($actor, $payload['certification_status'] ?? 'draft', $payload['review_notes'] ?? null);

            $dataset = ReportDataset::query()->create([
                'key' => $payload['key'],
                'name' => $payload['name'],
                'domain' => $payload['domain'],
                'description' => $this->nullableTrimmedString($payload['description'] ?? null),
                'source_references' => $sourceReferences,
                'grain' => trim((string) $payload['grain']),
                'approved_fields' => $approvedFields,
                'approved_filters' => $approvedFilters,
                'drilldown_paths' => $drilldownPaths,
                'masking_posture' => $maskingPosture,
                'freshness_expectation_minutes' => $this->nullableInt($payload['freshness_expectation_minutes'] ?? null),
                'owner_user_id' => $this->nullableInt($payload['owner_user_id'] ?? null),
                'version' => 1,
                'created_by_user_id' => $actor->id,
                'updated_by_user_id' => $actor->id,
                ...$governance,
            ]);

            $this->auditLogger->record(
                'reporting.dataset.created',
                $actor,
                [
                    'report_dataset_id' => $dataset->id,
                    'key' => $dataset->key,
                    'domain' => $dataset->domain,
                    'certification_status' => $dataset->certification_status,
                    'version' => $dataset->version,
                ],
                entityType: 'report_dataset',
                entityId: (string) $dataset->id,
            );

            return $this->findDatasetForView($actor, $dataset->id);
        });
    }

    /**
     * @param  DatasetPayload  $payload
     */
    public function updateDataset(User $actor, ReportDataset $dataset, array $payload): ReportDataset
    {
        return DB::transaction(function () use ($actor, $dataset, $payload): ReportDataset {
            $updates = [];

            if (array_key_exists('name', $payload)) {
                $updates['name'] = trim((string) $payload['name']);
            }

            if (array_key_exists('domain', $payload)) {
                $updates['domain'] = $payload['domain'];
            }

            if (array_key_exists('description', $payload)) {
                $updates['description'] = $this->nullableTrimmedString($payload['description']);
            }

            if (array_key_exists('source_references', $payload)) {
                $updates['source_references'] = $this->normalizeSourceReferences($payload['source_references'] ?? []);
            }

            if (array_key_exists('grain', $payload)) {
                $updates['grain'] = trim((string) $payload['grain']);
            }

            if (array_key_exists('approved_fields', $payload)) {
                $updates['approved_fields'] = $this->normalizeApprovedFields($payload['approved_fields'] ?? []);
            }

            if (array_key_exists('approved_filters', $payload)) {
                $updates['approved_filters'] = $this->normalizeApprovedFilters($payload['approved_filters'] ?? null);
            }

            if (array_key_exists('drilldown_paths', $payload)) {
                $updates['drilldown_paths'] = $this->normalizeDrilldownPaths($payload['drilldown_paths'] ?? null);
            }

            if (array_key_exists('masking_posture', $payload)) {
                $updates['masking_posture'] = $this->normalizeMaskingPosture($payload['masking_posture'] ?? []);
            }

            if (array_key_exists('freshness_expectation_minutes', $payload)) {
                $updates['freshness_expectation_minutes'] = $this->nullableInt($payload['freshness_expectation_minutes']);
            }

            if (array_key_exists('owner_user_id', $payload)) {
                $updates['owner_user_id'] = $this->nullableInt($payload['owner_user_id']);
            }

            $approvedFields = $updates['approved_fields'] ?? ($dataset->approved_fields ?? []);
            $approvedFilters = array_key_exists('approved_filters', $updates) ? $updates['approved_filters'] : ($dataset->approved_filters ?? null);
            $drilldownPaths = array_key_exists('drilldown_paths', $updates) ? $updates['drilldown_paths'] : ($dataset->drilldown_paths ?? null);
            $maskingPosture = $updates['masking_posture'] ?? ($dataset->masking_posture ?? []);

            $this->assertDatasetStructureIsValid($approvedFields, $approvedFilters, $drilldownPaths, $maskingPosture);

            $governance = $this->resolveGovernanceState(
                $actor,
                $payload['certification_status'] ?? $dataset->certification_status,
                array_key_exists('review_notes', $payload) ? $payload['review_notes'] : $dataset->review_notes,
            );

            $dataset->fill([
                ...$updates,
                ...$governance,
                'version' => $dataset->version + 1,
                'updated_by_user_id' => $actor->id,
            ]);
            $dataset->save();

            $this->auditLogger->record(
                'reporting.dataset.updated',
                $actor,
                [
                    'report_dataset_id' => $dataset->id,
                    'key' => $dataset->key,
                    'domain' => $dataset->domain,
                    'certification_status' => $dataset->certification_status,
                    'version' => $dataset->version,
                ],
                entityType: 'report_dataset',
                entityId: (string) $dataset->id,
            );

            return $this->findDatasetForView($actor, $dataset->id);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $sourceReferences
     * @return list<SourceReference>
     */
    private function normalizeSourceReferences(array $sourceReferences): array
    {
        return collect($sourceReferences)
            ->map(fn (array $reference): array => [
                'module' => trim((string) ($reference['module'] ?? '')),
                'entity' => trim((string) ($reference['entity'] ?? '')),
                'field' => $this->nullableTrimmedString($reference['field'] ?? null),
                'notes' => $this->nullableTrimmedString($reference['notes'] ?? null),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $approvedFields
     * @return list<ApprovedField>
     */
    private function normalizeApprovedFields(array $approvedFields): array
    {
        return collect($approvedFields)
            ->map(fn (array $field): array => [
                'key' => trim((string) ($field['key'] ?? '')),
                'label' => trim((string) ($field['label'] ?? '')),
                'type' => trim((string) ($field['type'] ?? '')),
                'description' => $this->nullableTrimmedString($field['description'] ?? null),
                'sensitive' => (bool) ($field['sensitive'] ?? false),
                'masking_strategy' => $this->nullableTrimmedString($field['masking_strategy'] ?? null),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>|null  $approvedFilters
     * @return list<ApprovedFilter>|null
     */
    private function normalizeApprovedFilters(?array $approvedFilters): ?array
    {
        if ($approvedFilters === null) {
            return null;
        }

        return collect($approvedFilters)
            ->map(fn (array $filter): array => [
                'key' => trim((string) ($filter['key'] ?? '')),
                'label' => trim((string) ($filter['label'] ?? '')),
                'type' => trim((string) ($filter['type'] ?? '')),
                'required' => (bool) ($filter['required'] ?? false),
                'operators' => collect($filter['operators'] ?? [])
                    ->map(fn (mixed $operator): string => trim((string) $operator))
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>|null  $drilldownPaths
     * @return list<DrilldownPath>|null
     */
    private function normalizeDrilldownPaths(?array $drilldownPaths): ?array
    {
        if ($drilldownPaths === null) {
            return null;
        }

        return collect($drilldownPaths)
            ->map(fn (array $path): array => [
                'key' => trim((string) ($path['key'] ?? '')),
                'label' => trim((string) ($path['label'] ?? '')),
                'target_dataset_key' => $this->nullableTrimmedString($path['target_dataset_key'] ?? null),
                'description' => $this->nullableTrimmedString($path['description'] ?? null),
                'allowed_filter_keys' => collect($path['allowed_filter_keys'] ?? [])
                    ->map(fn (mixed $filterKey): string => trim((string) $filterKey))
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $maskingPosture
     * @return MaskingPosture
     */
    private function normalizeMaskingPosture(array $maskingPosture): array
    {
        return [
            'default_strategy' => trim((string) ($maskingPosture['default_strategy'] ?? '')),
            'sensitive_field_keys' => collect($maskingPosture['sensitive_field_keys'] ?? [])
                ->map(fn (mixed $fieldKey): string => trim((string) $fieldKey))
                ->values()
                ->all(),
            'notes' => $this->nullableTrimmedString($maskingPosture['notes'] ?? null),
        ];
    }

    /**
     * @param  list<ApprovedField>  $approvedFields
     * @param  list<ApprovedFilter>|null  $approvedFilters
     * @param  list<DrilldownPath>|null  $drilldownPaths
     * @param  MaskingPosture  $maskingPosture
     */
    private function assertDatasetStructureIsValid(array $approvedFields, ?array $approvedFilters, ?array $drilldownPaths, array $maskingPosture): void
    {
        $fieldKeys = collect($approvedFields)->pluck('key');

        if ($fieldKeys->count() !== $fieldKeys->unique()->count()) {
            throw ValidationException::withMessages([
                'approved_fields' => 'Approved fields must use unique keys within a dataset definition.',
            ]);
        }

        foreach ($approvedFields as $index => $field) {
            if ($field['sensitive'] && ! in_array($field['masking_strategy'], ['redact', 'partial', 'aggregate_only'], true)) {
                throw ValidationException::withMessages([
                    'approved_fields.'.$index.'.masking_strategy' => 'Sensitive approved fields must define a masking strategy.',
                ]);
            }
        }

        $filterKeys = collect($approvedFilters ?? [])->pluck('key');

        if ($filterKeys->count() !== $filterKeys->unique()->count()) {
            throw ValidationException::withMessages([
                'approved_filters' => 'Approved filters must use unique keys within a dataset definition.',
            ]);
        }

        $unknownFilterKeys = $filterKeys->diff($fieldKeys);

        if ($unknownFilterKeys->isNotEmpty()) {
            throw ValidationException::withMessages([
                'approved_filters' => 'Approved filters must map to approved field keys. Unknown filter keys: '.$unknownFilterKeys->implode(', ').'.',
            ]);
        }

        $sensitiveFieldKeys = collect($maskingPosture['sensitive_field_keys'] ?? []);
        $unknownSensitiveKeys = $sensitiveFieldKeys->diff($fieldKeys);

        if ($unknownSensitiveKeys->isNotEmpty()) {
            throw ValidationException::withMessages([
                'masking_posture.sensitive_field_keys' => 'Masking posture can only reference approved field keys. Unknown keys: '.$unknownSensitiveKeys->implode(', ').'.',
            ]);
        }

        $insufficientlyMarkedSensitiveKeys = $sensitiveFieldKeys->filter(function (string $key) use ($approvedFields): bool {
            $matchingField = collect($approvedFields)->firstWhere('key', $key);

            return ! is_array($matchingField) || ($matchingField['sensitive'] ?? false) !== true;
        });

        if ($insufficientlyMarkedSensitiveKeys->isNotEmpty()) {
            throw ValidationException::withMessages([
                'masking_posture.sensitive_field_keys' => 'Masking posture keys must also be marked as sensitive approved fields. Invalid keys: '.$insufficientlyMarkedSensitiveKeys->implode(', ').'.',
            ]);
        }

        $drilldownKeys = collect($drilldownPaths ?? [])->pluck('key');

        if ($drilldownKeys->count() !== $drilldownKeys->unique()->count()) {
            throw ValidationException::withMessages([
                'drilldown_paths' => 'Drilldown paths must use unique keys within a dataset definition.',
            ]);
        }

        $allowedFilterKeys = collect($drilldownPaths ?? [])
            ->flatMap(fn (array $path): array => $path['allowed_filter_keys'] ?? []);
        $unknownAllowedFilterKeys = $allowedFilterKeys->diff($filterKeys);

        if ($unknownAllowedFilterKeys->isNotEmpty()) {
            throw ValidationException::withMessages([
                'drilldown_paths' => 'Drilldown paths can only reference approved filter keys. Unknown keys: '.$unknownAllowedFilterKeys->implode(', ').'.',
            ]);
        }
    }

    /**
     * @return array{
     *   certification_status: string,
     *   review_notes: string|null,
     *   reviewed_by_user_id: int|null,
     *   reviewed_at: Carbon|null,
     *   certified_by_user_id: int|null,
     *   certified_at: Carbon|null
     * }
     */
    private function resolveGovernanceState(User $actor, string $certificationStatus, ?string $reviewNotes): array
    {
        $normalizedStatus = trim($certificationStatus);
        $normalizedNotes = $this->nullableTrimmedString($reviewNotes);
        $now = now();

        if ($normalizedStatus === 'draft') {
            return [
                'certification_status' => 'draft',
                'review_notes' => $normalizedNotes,
                'reviewed_by_user_id' => null,
                'reviewed_at' => null,
                'certified_by_user_id' => null,
                'certified_at' => null,
            ];
        }

        return [
            'certification_status' => $normalizedStatus,
            'review_notes' => $normalizedNotes,
            'reviewed_by_user_id' => $actor->id,
            'reviewed_at' => $now,
            'certified_by_user_id' => $normalizedStatus === 'certified' ? $actor->id : null,
            'certified_at' => $normalizedStatus === 'certified' ? $now : null,
        ];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
