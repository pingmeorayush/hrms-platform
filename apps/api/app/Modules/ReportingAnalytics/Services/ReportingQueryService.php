<?php

namespace App\Modules\ReportingAnalytics\Services;

use App\Models\ReportDataset;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\ReportingAnalytics\Resources\ReportDatasetResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type QueryPayload array{
 *   filters?: array<string, mixed>,
 *   filter_operators?: array<string, string>,
 *   sort_by?: string,
 *   sort_direction?: string,
 *   drilldown_path?: string,
 *   page?: int|string,
 *   per_page?: int|string
 * }
 * @phpstan-type DatasetFilterDefinition array{
 *   key: string,
 *   label: string,
 *   type: string,
 *   required: bool,
 *   operators: list<string>
 * }
 * @phpstan-type DatasetDrilldownPath array{
 *   key: string,
 *   label: string,
 *   target_dataset_key: string|null,
 *   description: string|null,
 *   allowed_filter_keys: list<string>
 * }
 * @phpstan-type DatasetHandler array{
 *   domain: string,
 *   supported_fields: list<string>,
 *   default_sort_by: string,
 *   default_sort_direction: 'asc'|'desc',
 *   query: \Closure(User): Builder,
 *   filters: array<string, \Closure(Builder, string, mixed): void>,
 *   sorts: array<string, \Closure(Builder, string): void>,
 *   transform: \Closure(object): array<string, mixed>
 * }
 * @phpstan-type DatasetFieldDefinition array{
 *   key: string,
 *   label?: string,
 *   type?: string,
 *   description?: string,
 *   sensitive?: bool,
 *   masking_strategy?: 'redact'|'partial'|'aggregate_only'|null
 * }
 * @phpstan-type FieldVisibility array{
 *   key: string,
 *   exposure: 'full'|'masked'|'hidden',
 *   strategy: 'redact'|'partial'|'aggregate_only'|null
 * }
 */
class ReportingQueryService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly ReportingAccessScopeService $accessScopeService,
    ) {}

    /**
     * @param  QueryPayload  $payload
     * @return array{
     *   dataset: array<string, mixed>,
     *   items: list<array<string, mixed>>,
     *   meta: array<string, mixed>,
     *   filters: array<string, mixed>,
     *   freshness: array<string, int|string|null>,
     *   visibility: array{
     *     masked_field_keys: list<string>,
     *     hidden_field_keys: list<string>,
     *     drilldown_keys: list<string>
     *   }
     * }
     */
    public function query(User $actor, string $datasetKey, array $payload, bool $recordAudit = true): array
    {
        $dataset = $this->accessScopeService->resolveAccessibleDataset($actor, $datasetKey);
        $handler = $this->resolveHandler($dataset->key);

        if ($dataset->domain !== $handler['domain']) {
            throw ValidationException::withMessages([
                'dataset' => 'The reporting dataset domain does not match the registered query handler.',
            ]);
        }

        $this->assertDatasetQueryCompatibility($dataset, $handler);

        $query = $handler['query']($actor);
        $approvedFilters = collect($dataset->approved_filters ?? []);
        $requestedFilters = is_array($payload['filters'] ?? null) ? $payload['filters'] : [];
        $requestedOperators = is_array($payload['filter_operators'] ?? null) ? $payload['filter_operators'] : [];

        $appliedFilters = $this->applyFilters($query, $handler, $approvedFilters, $requestedFilters, $requestedOperators);

        $sortBy = is_string($payload['sort_by'] ?? null) ? $payload['sort_by'] : $handler['default_sort_by'];
        $sortDirection = is_string($payload['sort_direction'] ?? null) ? strtolower($payload['sort_direction']) : $handler['default_sort_direction'];
        $this->applySort($query, $handler, $dataset, $sortBy, $sortDirection);

        $perPage = is_numeric($payload['per_page'] ?? null) ? (int) $payload['per_page'] : 25;
        $page = is_numeric($payload['page'] ?? null) ? (int) $payload['page'] : null;
        $paginator = $page !== null
            ? $query->paginate($perPage, ['*'], 'page', $page)
            : $query->paginate($perPage);

        $drilldownPath = is_string($payload['drilldown_path'] ?? null) ? $payload['drilldown_path'] : null;
        $fieldVisibility = $this->resolveFieldVisibility($actor, $dataset);
        $drilldownPaths = $this->resolveAccessibleDrilldownPaths(
            $actor,
            $dataset,
            collect($dataset->drilldown_paths ?? []),
            $drilldownPath,
        );
        $items = $this->transformRows(
            $paginator,
            $dataset,
            $handler,
            $fieldVisibility,
            $drilldownPaths,
        );
        $maskedFieldKeys = collect($fieldVisibility)
            ->filter(fn (array $visibility): bool => $visibility['exposure'] === 'masked')
            ->keys()
            ->values()
            ->all();
        $hiddenFieldKeys = collect($fieldVisibility)
            ->filter(fn (array $visibility): bool => $visibility['exposure'] === 'hidden')
            ->keys()
            ->values()
            ->all();
        $drilldownKeys = $drilldownPaths->pluck('key')->values()->all();

        if ($recordAudit) {
            $this->auditLogger->record(
                'reporting.dataset.queried',
                $actor,
                [
                    'report_dataset_id' => $dataset->id,
                    'key' => $dataset->key,
                    'domain' => $dataset->domain,
                    'page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                    'filter_keys' => array_keys($appliedFilters),
                    'drilldown_path_requested' => $drilldownPath,
                    'drilldown_keys_returned' => $drilldownKeys,
                    'masked_field_keys' => $maskedFieldKeys,
                    'hidden_field_keys' => $hiddenFieldKeys,
                ],
                entityType: 'report_dataset',
                entityId: (string) $dataset->id,
            );
        }

        return [
            'dataset' => (new ReportDatasetResource($dataset))->toArray(request()),
            'items' => $items,
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'drilldown_path' => $drilldownPath,
            ],
            'filters' => [
                'available' => $dataset->approved_filters ?? [],
                'applied' => $appliedFilters,
            ],
            'freshness' => [
                'generated_at' => $this->accessScopeService->nowIsoString(),
                'expectation_minutes' => $dataset->freshness_expectation_minutes,
            ],
            'visibility' => [
                'masked_field_keys' => $maskedFieldKeys,
                'hidden_field_keys' => $hiddenFieldKeys,
                'drilldown_keys' => $drilldownKeys,
            ],
        ];
    }

    /**
     * @param  DatasetHandler  $handler
     */
    private function assertDatasetQueryCompatibility(ReportDataset $dataset, array $handler): void
    {
        $supportedFields = collect($handler['supported_fields']);
        $approvedFieldKeys = collect($dataset->approved_fields ?? [])->pluck('key');
        $unknownFieldKeys = $approvedFieldKeys->diff($supportedFields);

        if ($unknownFieldKeys->isNotEmpty()) {
            throw ValidationException::withMessages([
                'dataset' => 'The reporting dataset contains unsupported approved field keys for its query handler: '.$unknownFieldKeys->implode(', ').'.',
            ]);
        }

        $approvedFilterKeys = collect($dataset->approved_filters ?? [])->pluck('key');
        $unknownFilterKeys = $approvedFilterKeys->diff($supportedFields);

        if ($unknownFilterKeys->isNotEmpty()) {
            throw ValidationException::withMessages([
                'dataset' => 'The reporting dataset contains unsupported approved filter keys for its query handler: '.$unknownFilterKeys->implode(', ').'.',
            ]);
        }

        $unknownDrilldownAllowedKeys = collect($dataset->drilldown_paths ?? [])
            ->flatMap(fn (array $path): array => $path['allowed_filter_keys'] ?? [])
            ->diff($approvedFilterKeys);

        if ($unknownDrilldownAllowedKeys->isNotEmpty()) {
            throw ValidationException::withMessages([
                'dataset' => 'The reporting dataset contains drilldown filter keys that are not approved filters: '.$unknownDrilldownAllowedKeys->implode(', ').'.',
            ]);
        }
    }

    /**
     * @param  DatasetHandler  $handler
     * @param  Collection<int, DatasetFilterDefinition>  $approvedFilters
     * @param  array<string, mixed>  $requestedFilters
     * @param  array<string, string>  $requestedOperators
     * @return array<string, array{operator: string, value: mixed}>
     */
    private function applyFilters(Builder $query, array $handler, Collection $approvedFilters, array $requestedFilters, array $requestedOperators): array
    {
        $approvedFilterKeys = $approvedFilters->pluck('key')->all();
        $unknownRequestedFilters = array_diff(array_keys($requestedFilters), $approvedFilterKeys);

        if ($unknownRequestedFilters !== []) {
            throw ValidationException::withMessages([
                'filters' => 'Only approved reporting filters may be used. Unknown filters: '.implode(', ', $unknownRequestedFilters).'.',
            ]);
        }

        $appliedFilters = [];

        foreach ($approvedFilters as $filterDefinition) {
            $filterKey = $filterDefinition['key'];

            if (! array_key_exists($filterKey, $requestedFilters)) {
                continue;
            }

            $value = $requestedFilters[$filterKey];
            $operator = $requestedOperators[$filterKey] ?? $this->defaultOperatorForValue($value);

            if (! in_array($operator, $filterDefinition['operators'], true)) {
                throw ValidationException::withMessages([
                    'filter_operators.'.$filterKey => 'The selected operator is not approved for this reporting filter.',
                ]);
            }

            if (! array_key_exists($filterKey, $handler['filters'])) {
                throw ValidationException::withMessages([
                    'filters.'.$filterKey => 'This reporting filter is not yet implemented for the selected dataset.',
                ]);
            }

            $handler['filters'][$filterKey]($query, $operator, $value);
            $appliedFilters[$filterKey] = [
                'operator' => $operator,
                'value' => $value,
            ];
        }

        foreach ($approvedFilters->where('required', true) as $requiredFilter) {
            if (! array_key_exists($requiredFilter['key'], $appliedFilters)) {
                throw ValidationException::withMessages([
                    'filters.'.$requiredFilter['key'] => 'This reporting filter is required for the selected dataset.',
                ]);
            }
        }

        return $appliedFilters;
    }

    /**
     * @param  DatasetHandler  $handler
     */
    private function applySort(Builder $query, array $handler, ReportDataset $dataset, string $sortBy, string $sortDirection): void
    {
        $normalizedDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $approvedFieldKeys = collect($dataset->approved_fields ?? [])->pluck('key')->all();

        if (! in_array($sortBy, $approvedFieldKeys, true)) {
            throw ValidationException::withMessages([
                'sort_by' => 'Sorting is only allowed on approved reporting field keys.',
            ]);
        }

        if (! array_key_exists($sortBy, $handler['sorts'])) {
            throw ValidationException::withMessages([
                'sort_by' => 'Sorting is not implemented for the selected reporting field.',
            ]);
        }

        $handler['sorts'][$sortBy]($query, $normalizedDirection);
    }

    /**
     * @param  LengthAwarePaginator<object>  $paginator
     * @param  array<string, FieldVisibility>  $fieldVisibility
     * @param  Collection<int, DatasetDrilldownPath>  $drilldownPaths
     * @return list<array<string, mixed>>
     */
    private function transformRows(LengthAwarePaginator $paginator, ReportDataset $dataset, array $handler, array $fieldVisibility, Collection $drilldownPaths): array
    {
        $approvedFieldDefinitions = collect($dataset->approved_fields ?? [])
            ->filter(fn (mixed $field): bool => is_array($field) && is_string($field['key'] ?? null))
            ->values();

        return collect($paginator->items())
            ->map(function (object $row) use ($approvedFieldDefinitions, $fieldVisibility, $handler, $drilldownPaths): array {
                $transformed = $handler['transform']($row);
                $result = [];

                foreach ($approvedFieldDefinitions as $fieldDefinition) {
                    $fieldKey = $fieldDefinition['key'];
                    $result[$fieldKey] = $this->applyFieldVisibility(
                        $transformed[$fieldKey] ?? null,
                        $fieldVisibility[$fieldKey] ?? [
                            'key' => $fieldKey,
                            'exposure' => 'full',
                            'strategy' => null,
                        ],
                    );
                }

                $result['drilldowns'] = $drilldownPaths
                    ->map(function (array $path) use ($fieldVisibility, $transformed): array {
                        $filterPayload = [];

                        foreach ($path['allowed_filter_keys'] ?? [] as $filterKey) {
                            $visibility = $fieldVisibility[$filterKey] ?? null;

                            if (($visibility['exposure'] ?? 'full') !== 'full') {
                                continue;
                            }

                            if (array_key_exists($filterKey, $transformed) && $transformed[$filterKey] !== null && $transformed[$filterKey] !== '') {
                                $filterPayload[$filterKey] = $transformed[$filterKey];
                            }
                        }

                        return [
                            'key' => $path['key'],
                            'label' => $path['label'],
                            'target_dataset_key' => $path['target_dataset_key'],
                            'description' => $path['description'],
                            'filters' => $filterPayload,
                        ];
                    })
                    ->values()
                    ->all();

                return $result;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, FieldVisibility>
     */
    private function resolveFieldVisibility(User $actor, ReportDataset $dataset): array
    {
        return collect($dataset->approved_fields ?? [])
            ->filter(fn (mixed $field): bool => is_array($field) && is_string($field['key'] ?? null))
            ->mapWithKeys(fn (array $field): array => [
                $field['key'] => $this->accessScopeService->resolveFieldExposure($actor, $dataset, $field),
            ])
            ->all();
    }

    /**
     * @param  Collection<int, DatasetDrilldownPath>  $drilldownPaths
     * @return Collection<int, DatasetDrilldownPath>
     */
    private function resolveAccessibleDrilldownPaths(User $actor, ReportDataset $dataset, Collection $drilldownPaths, ?string $requestedDrilldownPath): Collection
    {
        $selectedDrilldownPaths = $requestedDrilldownPath === null
            ? $drilldownPaths
            : $drilldownPaths->where('key', $requestedDrilldownPath)->values();

        if ($requestedDrilldownPath !== null && $selectedDrilldownPaths->isEmpty()) {
            throw ValidationException::withMessages([
                'drilldown_path' => 'The requested drilldown path is not approved for this reporting dataset.',
            ]);
        }

        $targetDatasets = $this->accessScopeService->resolveTargetDatasets(
            $actor,
            $selectedDrilldownPaths->pluck('target_dataset_key')->all(),
        );

        return $selectedDrilldownPaths
            ->filter(fn (array $path): bool => $this->accessScopeService->canAccessDrilldownPath($actor, $dataset, $path, $targetDatasets))
            ->values();
    }

    /**
     * @param  FieldVisibility  $visibility
     */
    private function applyFieldVisibility(mixed $value, array $visibility): mixed
    {
        if ($visibility['exposure'] === 'full') {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return match ($visibility['strategy']) {
            'aggregate_only' => null,
            'partial' => $this->maskPartially($value),
            'redact' => 'REDACTED',
            default => null,
        };
    }

    private function maskPartially(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return 'REDACTED';
        }

        if (str_contains($value, '@')) {
            return $this->maskEmail($value);
        }

        $length = mb_strlen($value);

        if ($length <= 2) {
            return str_repeat('*', $length);
        }

        $firstCharacter = mb_substr($value, 0, 1);
        $lastCharacter = mb_substr($value, -1, 1);

        return $firstCharacter.str_repeat('*', max($length - 2, 1)).$lastCharacter;
    }

    private function maskEmail(string $email): string
    {
        [$localPart, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($domain === '') {
            return $this->maskPartially($email);
        }

        $localLength = mb_strlen($localPart);
        $visiblePrefix = $localLength > 0 ? mb_substr($localPart, 0, 1) : '';
        $maskedLocal = $visiblePrefix.str_repeat('*', max($localLength - 1, 3));

        return $maskedLocal.'@'.$domain;
    }

    private function defaultOperatorForValue(mixed $value): string
    {
        return is_array($value) ? 'in' : 'eq';
    }

    /**
     * @return DatasetHandler
     */
    private function resolveHandler(string $datasetKey): array
    {
        $handlers = [
            'workforce_headcount_snapshot' => $this->workforceDatasetHandler(),
            'attendance_daily_register' => $this->attendanceDatasetHandler(),
            'leave_request_register' => $this->leaveDatasetHandler(),
            'payroll_run_register' => $this->payrollRunDatasetHandler(),
            'recruitment_candidate_pipeline' => $this->recruitmentDatasetHandler(),
            'performance_review_status' => $this->performanceDatasetHandler(),
            'learning_assignment_targets' => $this->learningDatasetHandler(),
        ];

        if (! array_key_exists($datasetKey, $handlers)) {
            throw ValidationException::withMessages([
                'dataset' => 'The selected reporting dataset is not yet query-enabled.',
            ]);
        }

        return $handlers[$datasetKey];
    }

    /**
     * @return DatasetHandler
     */
    private function workforceDatasetHandler(): array
    {
        return [
            'domain' => 'workforce',
            'supported_fields' => [
                'employee_id',
                'employee_code',
                'employee_name',
                'employee_email',
                'department_name',
                'designation_name',
                'location_name',
                'manager_name',
                'employment_status',
                'employment_type',
                'date_of_joining',
            ],
            'default_sort_by' => 'employee_code',
            'default_sort_direction' => 'asc',
            'query' => function (User $actor): Builder {
                $query = DB::table('employees')
                    ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
                    ->leftJoin('designations', 'designations.id', '=', 'employees.designation_id')
                    ->leftJoin('locations', 'locations.id', '=', 'employees.location_id')
                    ->leftJoin('employees as managers', 'managers.id', '=', 'employees.manager_id')
                    ->where('employees.company_id', $actor->company_id)
                    ->select([
                        'employees.id as employee_id',
                        'employees.employee_code',
                        'employees.first_name',
                        'employees.middle_name',
                        'employees.last_name',
                        'employees.email as employee_email',
                        'employees.employment_status',
                        'employees.employment_type',
                        'employees.date_of_joining',
                        'departments.name as department_name',
                        'designations.name as designation_name',
                        'locations.name as location_name',
                        'managers.employee_code as manager_employee_code',
                        'managers.first_name as manager_first_name',
                        'managers.middle_name as manager_middle_name',
                        'managers.last_name as manager_last_name',
                    ]);

                $accessibleEmployeeIds = $this->accessScopeService->workforceEmployeeIds($actor);

                if (is_array($accessibleEmployeeIds)) {
                    if ($accessibleEmployeeIds === []) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('employees.id', $accessibleEmployeeIds);
                    }
                }

                return $query;
            },
            'filters' => [
                'employee_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'employees.employee_code', $operator, $value),
                'department_name' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'departments.name', $operator, $value),
                'location_name' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'locations.name', $operator, $value),
                'employment_status' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'employees.employment_status', $operator, $value),
                'employment_type' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'employees.employment_type', $operator, $value),
                'date_of_joining' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyComparableFilter($query, 'employees.date_of_joining', $operator, $value),
            ],
            'sorts' => [
                'employee_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.employee_code', $direction),
                'department_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('departments.name', $direction)->orderBy('employees.employee_code'),
                'location_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('locations.name', $direction)->orderBy('employees.employee_code'),
                'employment_status' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.employment_status', $direction)->orderBy('employees.employee_code'),
                'date_of_joining' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.date_of_joining', $direction)->orderBy('employees.employee_code'),
                'employee_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.first_name', $direction)->orderBy('employees.last_name', $direction),
                'designation_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('designations.name', $direction)->orderBy('employees.employee_code'),
                'manager_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('managers.first_name', $direction)->orderBy('managers.last_name', $direction),
                'employment_type' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.employment_type', $direction)->orderBy('employees.employee_code'),
                'employee_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.id', $direction),
                'employee_email' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.email', $direction),
            ],
            'transform' => fn (object $row): array => [
                'employee_id' => $row->employee_id,
                'employee_code' => $row->employee_code,
                'employee_name' => $this->fullName($row->first_name ?? null, $row->middle_name ?? null, $row->last_name ?? null),
                'employee_email' => $row->employee_email,
                'department_name' => $row->department_name,
                'designation_name' => $row->designation_name,
                'location_name' => $row->location_name,
                'manager_name' => $this->fullName($row->manager_first_name ?? null, $row->manager_middle_name ?? null, $row->manager_last_name ?? null),
                'manager_employee_code' => $row->manager_employee_code,
                'employment_status' => $row->employment_status,
                'employment_type' => $row->employment_type,
                'date_of_joining' => $row->date_of_joining,
            ],
        ];
    }

    /**
     * @return DatasetHandler
     */
    private function attendanceDatasetHandler(): array
    {
        return [
            'domain' => 'attendance',
            'supported_fields' => [
                'attendance_record_id',
                'employee_id',
                'employee_code',
                'employee_name',
                'attendance_date',
                'primary_status',
                'worked_minutes',
                'is_late',
                'late_minutes',
                'overtime_minutes',
                'department_name',
            ],
            'default_sort_by' => 'attendance_date',
            'default_sort_direction' => 'desc',
            'query' => function (User $actor): Builder {
                $query = DB::table('attendance_records')
                    ->join('employees', 'employees.id', '=', 'attendance_records.employee_id')
                    ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
                    ->where('attendance_records.company_id', $actor->company_id)
                    ->select([
                        'attendance_records.id as attendance_record_id',
                        'attendance_records.employee_id',
                        'attendance_records.attendance_date',
                        'attendance_records.primary_status',
                        'attendance_records.worked_minutes',
                        'attendance_records.is_late',
                        'attendance_records.late_minutes',
                        'attendance_records.overtime_minutes',
                        'employees.employee_code',
                        'employees.first_name',
                        'employees.middle_name',
                        'employees.last_name',
                        'departments.name as department_name',
                    ]);

                $accessibleEmployeeIds = $this->accessScopeService->attendanceEmployeeIds($actor);

                if (is_array($accessibleEmployeeIds)) {
                    if ($accessibleEmployeeIds === []) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('attendance_records.employee_id', $accessibleEmployeeIds);
                    }
                }

                return $query;
            },
            'filters' => [
                'employee_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'employees.employee_code', $operator, $value),
                'attendance_date' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyComparableFilter($query, 'attendance_records.attendance_date', $operator, $value),
                'primary_status' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'attendance_records.primary_status', $operator, $value),
                'department_name' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'departments.name', $operator, $value),
            ],
            'sorts' => [
                'attendance_date' => fn (Builder $query, string $direction): Builder => $query->orderBy('attendance_records.attendance_date', $direction)->orderBy('employees.employee_code'),
                'employee_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.employee_code', $direction),
                'primary_status' => fn (Builder $query, string $direction): Builder => $query->orderBy('attendance_records.primary_status', $direction)->orderBy('attendance_records.attendance_date', 'desc'),
                'worked_minutes' => fn (Builder $query, string $direction): Builder => $query->orderBy('attendance_records.worked_minutes', $direction),
                'overtime_minutes' => fn (Builder $query, string $direction): Builder => $query->orderBy('attendance_records.overtime_minutes', $direction),
                'employee_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.first_name', $direction)->orderBy('employees.last_name', $direction),
                'department_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('departments.name', $direction)->orderBy('attendance_records.attendance_date', 'desc'),
                'late_minutes' => fn (Builder $query, string $direction): Builder => $query->orderBy('attendance_records.late_minutes', $direction),
                'attendance_record_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('attendance_records.id', $direction),
                'employee_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('attendance_records.employee_id', $direction),
                'is_late' => fn (Builder $query, string $direction): Builder => $query->orderBy('attendance_records.is_late', $direction),
            ],
            'transform' => fn (object $row): array => [
                'attendance_record_id' => $row->attendance_record_id,
                'employee_id' => $row->employee_id,
                'employee_code' => $row->employee_code,
                'employee_name' => $this->fullName($row->first_name ?? null, $row->middle_name ?? null, $row->last_name ?? null),
                'attendance_date' => $row->attendance_date,
                'primary_status' => $row->primary_status,
                'worked_minutes' => $row->worked_minutes,
                'is_late' => (bool) $row->is_late,
                'late_minutes' => $row->late_minutes,
                'overtime_minutes' => $row->overtime_minutes,
                'department_name' => $row->department_name,
            ],
        ];
    }

    /**
     * @return DatasetHandler
     */
    private function leaveDatasetHandler(): array
    {
        return [
            'domain' => 'leave',
            'supported_fields' => [
                'leave_request_id',
                'employee_id',
                'employee_code',
                'employee_name',
                'leave_type_name',
                'department_name',
                'start_date',
                'end_date',
                'total_days',
                'status',
                'attendance_sync_status',
            ],
            'default_sort_by' => 'start_date',
            'default_sort_direction' => 'desc',
            'query' => function (User $actor): Builder {
                $query = DB::table('leave_requests')
                    ->join('employees', 'employees.id', '=', 'leave_requests.employee_id')
                    ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
                    ->leftJoin('departments', 'departments.id', '=', 'leave_requests.department_id')
                    ->where('leave_requests.company_id', $actor->company_id)
                    ->select([
                        'leave_requests.id as leave_request_id',
                        'leave_requests.employee_id',
                        'leave_requests.start_date',
                        'leave_requests.end_date',
                        'leave_requests.total_days',
                        'leave_requests.status',
                        'leave_requests.attendance_sync_status',
                        'employees.employee_code',
                        'employees.first_name',
                        'employees.middle_name',
                        'employees.last_name',
                        'leave_types.name as leave_type_name',
                        'departments.name as department_name',
                    ]);

                $accessibleEmployeeIds = $this->accessScopeService->leaveEmployeeIds($actor);

                if (is_array($accessibleEmployeeIds)) {
                    if ($accessibleEmployeeIds === []) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('leave_requests.employee_id', $accessibleEmployeeIds);
                    }
                }

                return $query;
            },
            'filters' => [
                'employee_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'employees.employee_code', $operator, $value),
                'leave_type_name' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'leave_types.name', $operator, $value),
                'department_name' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'departments.name', $operator, $value),
                'start_date' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyComparableFilter($query, 'leave_requests.start_date', $operator, $value),
                'status' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'leave_requests.status', $operator, $value),
            ],
            'sorts' => [
                'start_date' => fn (Builder $query, string $direction): Builder => $query->orderBy('leave_requests.start_date', $direction)->orderBy('employees.employee_code'),
                'end_date' => fn (Builder $query, string $direction): Builder => $query->orderBy('leave_requests.end_date', $direction)->orderBy('employees.employee_code'),
                'employee_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.employee_code', $direction),
                'status' => fn (Builder $query, string $direction): Builder => $query->orderBy('leave_requests.status', $direction)->orderBy('leave_requests.start_date', 'desc'),
                'leave_type_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('leave_types.name', $direction)->orderBy('leave_requests.start_date', 'desc'),
                'employee_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.first_name', $direction)->orderBy('employees.last_name', $direction),
                'total_days' => fn (Builder $query, string $direction): Builder => $query->orderBy('leave_requests.total_days', $direction),
                'department_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('departments.name', $direction)->orderBy('leave_requests.start_date', 'desc'),
                'leave_request_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('leave_requests.id', $direction),
                'employee_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('leave_requests.employee_id', $direction),
                'attendance_sync_status' => fn (Builder $query, string $direction): Builder => $query->orderBy('leave_requests.attendance_sync_status', $direction),
            ],
            'transform' => fn (object $row): array => [
                'leave_request_id' => $row->leave_request_id,
                'employee_id' => $row->employee_id,
                'employee_code' => $row->employee_code,
                'employee_name' => $this->fullName($row->first_name ?? null, $row->middle_name ?? null, $row->last_name ?? null),
                'leave_type_name' => $row->leave_type_name,
                'department_name' => $row->department_name,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'total_days' => (float) $row->total_days,
                'status' => $row->status,
                'attendance_sync_status' => $row->attendance_sync_status,
            ],
        ];
    }

    /**
     * @return DatasetHandler
     */
    private function payrollRunDatasetHandler(): array
    {
        return [
            'domain' => 'payroll',
            'supported_fields' => [
                'payroll_run_id',
                'period_name',
                'run_name',
                'frequency',
                'status',
                'start_date',
                'end_date',
                'prepared_at',
                'calculated_at',
                'approved_at',
                'locked_at',
            ],
            'default_sort_by' => 'start_date',
            'default_sort_direction' => 'desc',
            'query' => fn (User $actor): Builder => DB::table('payroll_runs')
                ->join('payroll_periods', 'payroll_periods.id', '=', 'payroll_runs.payroll_period_id')
                ->where('payroll_runs.company_id', $actor->company_id)
                ->select([
                    'payroll_runs.id as payroll_run_id',
                    'payroll_periods.name as period_name',
                    'payroll_runs.name as run_name',
                    'payroll_runs.frequency',
                    'payroll_runs.status',
                    'payroll_runs.start_date',
                    'payroll_runs.end_date',
                    'payroll_runs.prepared_at',
                    'payroll_runs.calculated_at',
                    'payroll_runs.approved_at',
                    'payroll_runs.locked_at',
                ]),
            'filters' => [
                'period_name' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'payroll_periods.name', $operator, $value),
                'run_name' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'payroll_runs.name', $operator, $value),
                'frequency' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'payroll_runs.frequency', $operator, $value),
                'status' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'payroll_runs.status', $operator, $value),
                'start_date' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyComparableFilter($query, 'payroll_runs.start_date', $operator, $value),
            ],
            'sorts' => [
                'start_date' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.start_date', $direction)->orderBy('payroll_runs.id', 'desc'),
                'end_date' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.end_date', $direction)->orderBy('payroll_runs.id', 'desc'),
                'status' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.status', $direction)->orderBy('payroll_runs.start_date', 'desc'),
                'run_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.name', $direction),
                'period_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_periods.name', $direction),
                'frequency' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.frequency', $direction),
                'payroll_run_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.id', $direction),
                'prepared_at' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.prepared_at', $direction),
                'calculated_at' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.calculated_at', $direction),
                'approved_at' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.approved_at', $direction),
                'locked_at' => fn (Builder $query, string $direction): Builder => $query->orderBy('payroll_runs.locked_at', $direction),
            ],
            'transform' => fn (object $row): array => [
                'payroll_run_id' => $row->payroll_run_id,
                'period_name' => $row->period_name,
                'run_name' => $row->run_name,
                'frequency' => $row->frequency,
                'status' => $row->status,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'prepared_at' => $row->prepared_at,
                'calculated_at' => $row->calculated_at,
                'approved_at' => $row->approved_at,
                'locked_at' => $row->locked_at,
            ],
        ];
    }

    /**
     * @return DatasetHandler
     */
    private function recruitmentDatasetHandler(): array
    {
        return [
            'domain' => 'recruitment',
            'supported_fields' => [
                'candidate_id',
                'candidate_code',
                'candidate_name',
                'requisition_code',
                'requisition_title',
                'recruiter_name',
                'current_stage',
                'status',
                'source',
                'total_experience_years',
                'stage_entered_at',
            ],
            'default_sort_by' => 'stage_entered_at',
            'default_sort_direction' => 'desc',
            'query' => function (User $actor): Builder {
                $query = DB::table('candidates')
                    ->leftJoin('job_requisitions', 'job_requisitions.id', '=', 'candidates.job_requisition_id')
                    ->leftJoin('users as recruiters', 'recruiters.id', '=', 'candidates.recruiter_user_id')
                    ->where('candidates.company_id', $actor->company_id)
                    ->select([
                        'candidates.id as candidate_id',
                        'candidates.candidate_code',
                        'candidates.first_name',
                        'candidates.last_name',
                        'candidates.current_stage',
                        'candidates.status',
                        'candidates.source',
                        'candidates.total_experience_years',
                        'candidates.stage_entered_at',
                        'job_requisitions.requisition_code',
                        'job_requisitions.title as requisition_title',
                        'job_requisitions.hiring_manager_employee_id',
                        'recruiters.name as recruiter_name',
                        'candidates.recruiter_user_id',
                    ]);

                if (! $actor->can('recruitment.manage')) {
                    $actorEmployeeId = $this->accessScopeService->linkedEmployeeId($actor);

                    $query->where(function (Builder $builder) use ($actor, $actorEmployeeId): void {
                        $builder->where('candidates.recruiter_user_id', $actor->id)
                            ->orWhere('job_requisitions.recruiter_user_id', $actor->id);

                        if ($actorEmployeeId !== null) {
                            $builder->orWhere('job_requisitions.hiring_manager_employee_id', $actorEmployeeId);
                        }
                    });
                }

                return $query;
            },
            'filters' => [
                'candidate_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'candidates.candidate_code', $operator, $value),
                'requisition_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'job_requisitions.requisition_code', $operator, $value),
                'current_stage' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'candidates.current_stage', $operator, $value),
                'status' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'candidates.status', $operator, $value),
                'source' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'candidates.source', $operator, $value),
            ],
            'sorts' => [
                'stage_entered_at' => fn (Builder $query, string $direction): Builder => $query->orderBy('candidates.stage_entered_at', $direction)->orderBy('candidates.id', 'desc'),
                'candidate_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('candidates.candidate_code', $direction),
                'current_stage' => fn (Builder $query, string $direction): Builder => $query->orderBy('candidates.current_stage', $direction)->orderBy('candidates.stage_entered_at', 'desc'),
                'status' => fn (Builder $query, string $direction): Builder => $query->orderBy('candidates.status', $direction),
                'source' => fn (Builder $query, string $direction): Builder => $query->orderBy('candidates.source', $direction),
                'candidate_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('candidates.first_name', $direction)->orderBy('candidates.last_name', $direction),
                'requisition_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('job_requisitions.requisition_code', $direction),
                'requisition_title' => fn (Builder $query, string $direction): Builder => $query->orderBy('job_requisitions.title', $direction),
                'recruiter_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('recruiters.name', $direction),
                'candidate_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('candidates.id', $direction),
                'total_experience_years' => fn (Builder $query, string $direction): Builder => $query->orderBy('candidates.total_experience_years', $direction),
            ],
            'transform' => fn (object $row): array => [
                'candidate_id' => $row->candidate_id,
                'candidate_code' => $row->candidate_code,
                'candidate_name' => $this->fullName($row->first_name ?? null, null, $row->last_name ?? null),
                'requisition_code' => $row->requisition_code,
                'requisition_title' => $row->requisition_title,
                'recruiter_name' => $row->recruiter_name,
                'current_stage' => $row->current_stage,
                'status' => $row->status,
                'source' => $row->source,
                'total_experience_years' => $row->total_experience_years,
                'stage_entered_at' => $row->stage_entered_at,
            ],
        ];
    }

    /**
     * @return DatasetHandler
     */
    private function performanceDatasetHandler(): array
    {
        return [
            'domain' => 'performance',
            'supported_fields' => [
                'performance_review_id',
                'employee_id',
                'employee_code',
                'employee_name',
                'review_cycle_code',
                'review_cycle_name',
                'manager_name',
                'status',
                'launched_at',
                'published_at',
                'reopen_count',
            ],
            'default_sort_by' => 'launched_at',
            'default_sort_direction' => 'desc',
            'query' => function (User $actor): Builder {
                $query = DB::table('performance_reviews')
                    ->join('employees', 'employees.id', '=', 'performance_reviews.employee_id')
                    ->join('performance_review_cycles', 'performance_review_cycles.id', '=', 'performance_reviews.performance_review_cycle_id')
                    ->leftJoin('employees as managers', 'managers.id', '=', 'performance_reviews.manager_employee_id')
                    ->where('performance_reviews.company_id', $actor->company_id)
                    ->select([
                        'performance_reviews.id as performance_review_id',
                        'performance_reviews.employee_id',
                        'performance_reviews.status',
                        'performance_reviews.launched_at',
                        'performance_reviews.published_at',
                        'performance_reviews.reopen_count',
                        'employees.employee_code',
                        'employees.first_name',
                        'employees.middle_name',
                        'employees.last_name',
                        'performance_review_cycles.code as review_cycle_code',
                        'performance_review_cycles.name as review_cycle_name',
                        'managers.first_name as manager_first_name',
                        'managers.middle_name as manager_middle_name',
                        'managers.last_name as manager_last_name',
                    ]);

                if (! $actor->canAny(['performance.manage', 'performance.calibrate'])) {
                    $actorEmployeeId = $this->accessScopeService->linkedEmployeeId($actor);

                    $query->where(function (Builder $builder) use ($actor, $actorEmployeeId): void {
                        if ($actorEmployeeId !== null) {
                            $builder->orWhere('performance_reviews.employee_id', $actorEmployeeId)
                                ->orWhere('performance_reviews.manager_employee_id', $actorEmployeeId);
                        }

                        $builder->orWhereJsonContains('performance_reviews.reviewer_user_ids', $actor->id);
                    });
                }

                return $query;
            },
            'filters' => [
                'employee_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'employees.employee_code', $operator, $value),
                'review_cycle_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'performance_review_cycles.code', $operator, $value),
                'status' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'performance_reviews.status', $operator, $value),
                'launched_at' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyComparableFilter($query, 'performance_reviews.launched_at', $operator, $value),
            ],
            'sorts' => [
                'launched_at' => fn (Builder $query, string $direction): Builder => $query->orderBy('performance_reviews.launched_at', $direction)->orderBy('performance_reviews.id', 'desc'),
                'published_at' => fn (Builder $query, string $direction): Builder => $query->orderBy('performance_reviews.published_at', $direction)->orderBy('performance_reviews.id', 'desc'),
                'status' => fn (Builder $query, string $direction): Builder => $query->orderBy('performance_reviews.status', $direction),
                'employee_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.employee_code', $direction),
                'employee_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.first_name', $direction)->orderBy('employees.last_name', $direction),
                'review_cycle_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('performance_review_cycles.code', $direction),
                'review_cycle_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('performance_review_cycles.name', $direction),
                'manager_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('managers.first_name', $direction)->orderBy('managers.last_name', $direction),
                'performance_review_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('performance_reviews.id', $direction),
                'employee_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('performance_reviews.employee_id', $direction),
                'reopen_count' => fn (Builder $query, string $direction): Builder => $query->orderBy('performance_reviews.reopen_count', $direction),
            ],
            'transform' => fn (object $row): array => [
                'performance_review_id' => $row->performance_review_id,
                'employee_id' => $row->employee_id,
                'employee_code' => $row->employee_code,
                'employee_name' => $this->fullName($row->first_name ?? null, $row->middle_name ?? null, $row->last_name ?? null),
                'review_cycle_code' => $row->review_cycle_code,
                'review_cycle_name' => $row->review_cycle_name,
                'manager_name' => $this->fullName($row->manager_first_name ?? null, $row->manager_middle_name ?? null, $row->manager_last_name ?? null),
                'status' => $row->status,
                'launched_at' => $row->launched_at,
                'published_at' => $row->published_at,
                'reopen_count' => (int) $row->reopen_count,
            ],
        ];
    }

    /**
     * @return DatasetHandler
     */
    private function learningDatasetHandler(): array
    {
        return [
            'domain' => 'learning',
            'supported_fields' => [
                'learning_target_id',
                'employee_id',
                'employee_code',
                'employee_name',
                'learning_item_code',
                'learning_item_title',
                'status',
                'due_on',
                'renewal_due_on',
                'completion_progress_percent',
                'department_name',
            ],
            'default_sort_by' => 'due_on',
            'default_sort_direction' => 'asc',
            'query' => function (User $actor): Builder {
                $query = DB::table('learning_assignment_targets')
                    ->join('employees', 'employees.id', '=', 'learning_assignment_targets.employee_id')
                    ->join('learning_items', 'learning_items.id', '=', 'learning_assignment_targets.learning_item_id')
                    ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
                    ->where('learning_assignment_targets.company_id', $actor->company_id)
                    ->select([
                        'learning_assignment_targets.id as learning_target_id',
                        'learning_assignment_targets.employee_id',
                        'learning_assignment_targets.status',
                        'learning_assignment_targets.due_on',
                        'learning_assignment_targets.renewal_due_on',
                        'learning_assignment_targets.completion_progress_percent',
                        'employees.employee_code',
                        'employees.first_name',
                        'employees.middle_name',
                        'employees.last_name',
                        'learning_items.code as learning_item_code',
                        'learning_items.title as learning_item_title',
                        'departments.name as department_name',
                    ]);

                $accessibleEmployeeIds = $this->accessScopeService->learningEmployeeIds($actor);

                if (is_array($accessibleEmployeeIds)) {
                    if ($accessibleEmployeeIds === []) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('learning_assignment_targets.employee_id', $accessibleEmployeeIds);
                    }
                }

                return $query;
            },
            'filters' => [
                'employee_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'employees.employee_code', $operator, $value),
                'learning_item_code' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'learning_items.code', $operator, $value),
                'status' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyScalarFilter($query, 'learning_assignment_targets.status', $operator, $value),
                'due_on' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyComparableFilter($query, 'learning_assignment_targets.due_on', $operator, $value),
                'department_name' => fn (Builder $query, string $operator, mixed $value): mixed => $this->applyStringFilter($query, 'departments.name', $operator, $value),
            ],
            'sorts' => [
                'due_on' => fn (Builder $query, string $direction): Builder => $query->orderBy('learning_assignment_targets.due_on', $direction)->orderBy('employees.employee_code'),
                'renewal_due_on' => fn (Builder $query, string $direction): Builder => $query->orderBy('learning_assignment_targets.renewal_due_on', $direction),
                'status' => fn (Builder $query, string $direction): Builder => $query->orderBy('learning_assignment_targets.status', $direction),
                'employee_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.employee_code', $direction),
                'employee_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('employees.first_name', $direction)->orderBy('employees.last_name', $direction),
                'learning_item_code' => fn (Builder $query, string $direction): Builder => $query->orderBy('learning_items.code', $direction),
                'learning_item_title' => fn (Builder $query, string $direction): Builder => $query->orderBy('learning_items.title', $direction),
                'department_name' => fn (Builder $query, string $direction): Builder => $query->orderBy('departments.name', $direction),
                'learning_target_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('learning_assignment_targets.id', $direction),
                'employee_id' => fn (Builder $query, string $direction): Builder => $query->orderBy('learning_assignment_targets.employee_id', $direction),
                'completion_progress_percent' => fn (Builder $query, string $direction): Builder => $query->orderBy('learning_assignment_targets.completion_progress_percent', $direction),
            ],
            'transform' => fn (object $row): array => [
                'learning_target_id' => $row->learning_target_id,
                'employee_id' => $row->employee_id,
                'employee_code' => $row->employee_code,
                'employee_name' => $this->fullName($row->first_name ?? null, $row->middle_name ?? null, $row->last_name ?? null),
                'learning_item_code' => $row->learning_item_code,
                'learning_item_title' => $row->learning_item_title,
                'status' => $row->status,
                'due_on' => $row->due_on,
                'renewal_due_on' => $row->renewal_due_on,
                'completion_progress_percent' => (int) $row->completion_progress_percent,
                'department_name' => $row->department_name,
            ],
        ];
    }

    private function applyScalarFilter(Builder $query, string $column, string $operator, mixed $value): void
    {
        match ($operator) {
            'eq' => $query->where($column, '=', $value),
            'neq' => $query->where($column, '!=', $value),
            'in' => $query->whereIn($column, $this->normalizeArray($value)),
            'not_in' => $query->whereNotIn($column, $this->normalizeArray($value)),
            default => throw ValidationException::withMessages([
                'filters' => 'Unsupported scalar filter operator: '.$operator.'.',
            ]),
        };
    }

    private function applyStringFilter(Builder $query, string $column, string $operator, mixed $value): void
    {
        $stringValue = is_array($value) ? null : (string) $value;

        match ($operator) {
            'eq' => $query->where($column, '=', $stringValue),
            'neq' => $query->where($column, '!=', $stringValue),
            'contains' => $query->where($column, 'like', '%'.$stringValue.'%'),
            'starts_with' => $query->where($column, 'like', $stringValue.'%'),
            'ends_with' => $query->where($column, 'like', '%'.$stringValue),
            'in' => $query->whereIn($column, $this->normalizeArray($value)),
            'not_in' => $query->whereNotIn($column, $this->normalizeArray($value)),
            default => $this->applyScalarFilter($query, $column, $operator, $value),
        };
    }

    private function applyComparableFilter(Builder $query, string $column, string $operator, mixed $value): void
    {
        match ($operator) {
            'eq' => $query->where($column, '=', $value),
            'neq' => $query->where($column, '!=', $value),
            'lt' => $query->where($column, '<', $value),
            'lte' => $query->where($column, '<=', $value),
            'gt' => $query->where($column, '>', $value),
            'gte' => $query->where($column, '>=', $value),
            'between', 'date_between' => $this->applyBetweenFilter($query, $column, $value),
            'in' => $query->whereIn($column, $this->normalizeArray($value)),
            default => throw ValidationException::withMessages([
                'filters' => 'Unsupported comparable filter operator: '.$operator.'.',
            ]),
        };
    }

    private function applyBetweenFilter(Builder $query, string $column, mixed $value): void
    {
        $values = $this->normalizeArray($value);

        if (count($values) !== 2) {
            throw ValidationException::withMessages([
                'filters' => 'Between filters require exactly two values.',
            ]);
        }

        $query->whereBetween($column, [$values[0], $values[1]]);
    }

    /**
     * @return list<mixed>
     */
    private function normalizeArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [$value];
        }

        return array_values($value);
    }

    private function fullName(?string $firstName, ?string $middleName, ?string $lastName): string
    {
        return trim(implode(' ', array_values(array_filter([
            $firstName,
            $middleName,
            $lastName,
        ], fn (?string $value): bool => $value !== null && trim($value) !== ''))));
    }
}
