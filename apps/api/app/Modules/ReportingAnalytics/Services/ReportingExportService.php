<?php

namespace App\Modules\ReportingAnalytics\Services;

use App\Models\ReportDataset;
use App\Models\ReportExport;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Notifications\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * @phpstan-type ExportQueryPayload array{
 *   filters?: array<string, mixed>,
 *   filter_operators?: array<string, string>,
 *   sort_by?: string,
 *   sort_direction?: string,
 *   drilldown_path?: string
 * }
 * @phpstan-type ExportStorePayload array{
 *   dataset_key: string,
 *   format?: string,
 *   execution_mode?: string,
 *   delivery_target?: string,
 *   filters?: array<string, mixed>,
 *   filter_operators?: array<string, string>,
 *   sort_by?: string,
 *   sort_direction?: string,
 *   drilldown_path?: string
 * }
 */
class ReportingExportService
{
    public function __construct(
        private readonly ReportingQueryService $reportingQueryService,
        private readonly ReportingAccessScopeService $accessScopeService,
        private readonly NotificationService $notificationService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function searchExports(User $actor, array $filters): LengthAwarePaginator
    {
        $query = ReportExport::query()
            ->with(['reportDataset', 'requestedBy'])
            ->where('company_id', $actor->company_id);

        if (! $actor->canAny(['reporting.manage', 'reporting.certify'])) {
            $query->where('requested_by_user_id', $actor->id);
        }

        if (is_string($filters['status'] ?? null) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (is_string($filters['dataset_key'] ?? null) && $filters['dataset_key'] !== '') {
            $query->whereHas('reportDataset', fn (Builder $builder): Builder => $builder->where('key', $filters['dataset_key']));
        }

        if ((bool) ($filters['requested_by_me'] ?? false)) {
            $query->where('requested_by_user_id', $actor->id);
        }

        $perPage = is_numeric($filters['per_page'] ?? null) ? (int) $filters['per_page'] : 20;
        $exports = $query
            ->orderByDesc('requested_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        $exports->setCollection(
            $exports->getCollection()->map(fn (ReportExport $export): ReportExport => $this->normalizeExpiration($export, $actor))
        );

        $this->auditLogger->record(
            eventType: 'reporting.export.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'result_count' => count($exports->items()),
            ],
            entityType: 'report_export',
            entityId: null,
        );

        return $exports;
    }

    /**
     * @param  ExportStorePayload  $payload
     */
    public function requestExport(
        User $requester,
        array $payload,
        ?User $auditActor = null,
        bool $notifyOnCompletion = false,
    ): ReportExport {
        $auditActor ??= $requester;
        $dataset = $this->accessScopeService->resolveAccessibleDataset($requester, $payload['dataset_key']);
        $format = is_string($payload['format'] ?? null) ? $payload['format'] : 'csv';
        $executionMode = is_string($payload['execution_mode'] ?? null) ? $payload['execution_mode'] : 'auto';
        $deliveryTarget = is_string($payload['delivery_target'] ?? null) ? $payload['delivery_target'] : 'requestor_download';
        $queryPayload = $this->extractQueryPayload($payload);
        $preview = $this->reportingQueryService->query(
            $requester,
            $dataset->key,
            $queryPayload + ['page' => 1, 'per_page' => $this->maxExportRows()],
            false,
        );
        $total = (int) ($preview['meta']['total'] ?? 0);

        if ($total > $this->maxExportRows()) {
            throw ValidationException::withMessages([
                'dataset_key' => ['This reporting export exceeds the governed row limit for approved export delivery.'],
            ]);
        }

        if ($executionMode === 'sync' && $total > $this->syncRowLimit()) {
            throw ValidationException::withMessages([
                'execution_mode' => ['This reporting export exceeds the synchronous export limit. Use async or auto instead.'],
            ]);
        }

        $status = $executionMode === 'async' || ($executionMode === 'auto' && $total > $this->syncRowLimit())
            ? 'queued'
            : 'completed';

        $export = ReportExport::query()->create([
            'company_id' => $requester->company_id,
            'report_dataset_id' => $dataset->id,
            'requested_by_user_id' => $requester->id,
            'export_uuid' => (string) Str::uuid(),
            'status' => $status,
            'format' => $format,
            'execution_mode' => $status === 'queued' ? 'async' : 'sync',
            'delivery_target' => $deliveryTarget,
            'requested_filters' => $queryPayload['filters'] ?? [],
            'requested_filter_operators' => $queryPayload['filter_operators'] ?? [],
            'sort_by' => $queryPayload['sort_by'] ?? null,
            'sort_direction' => $queryPayload['sort_direction'] ?? null,
            'drilldown_path' => $queryPayload['drilldown_path'] ?? null,
            'estimated_row_count' => $total,
            'visibility_posture' => $preview['visibility'] ?? [],
            'freshness_snapshot' => $preview['freshness'] ?? [],
            'requested_at' => now(),
            'created_by_user_id' => $auditActor->id,
            'updated_by_user_id' => $auditActor->id,
        ]);

        $this->auditLogger->record(
            eventType: 'reporting.export.requested',
            actor: $auditActor,
            metadata: [
                'report_export_id' => $export->id,
                'report_dataset_id' => $dataset->id,
                'dataset_key' => $dataset->key,
                'format' => $format,
                'execution_mode' => $executionMode,
                'resolved_status' => $status,
                'estimated_row_count' => $total,
            ],
            entityType: 'report_export',
            entityId: (string) $export->id,
        );

        if ($status === 'queued') {
            $this->auditLogger->record(
                eventType: 'reporting.export.queued',
                actor: $auditActor,
                metadata: [
                    'report_export_id' => $export->id,
                    'report_dataset_id' => $dataset->id,
                    'dataset_key' => $dataset->key,
                    'estimated_row_count' => $total,
                ],
                entityType: 'report_export',
                entityId: (string) $export->id,
            );

            return $export->load(['reportDataset', 'requestedBy']);
        }

        $export = $this->finalizeCompletedExport($export, $dataset, $preview, $auditActor);

        if ($notifyOnCompletion) {
            $this->sendCompletionNotification($requester, $export, $dataset, true, null, $auditActor);
        }

        return $export;
    }

    public function showExport(User $actor, int $reportExportId): ReportExport
    {
        $export = $this->resolveExportForView($actor, $reportExportId);
        $export = $this->normalizeExpiration($export, $actor);

        $this->auditLogger->record(
            eventType: 'reporting.export.viewed',
            actor: $actor,
            metadata: [
                'report_export_id' => $export->id,
                'status' => $export->status,
                'report_dataset_id' => $export->report_dataset_id,
            ],
            entityType: 'report_export',
            entityId: (string) $export->id,
        );

        return $export;
    }

    public function processExport(User $actor, int $reportExportId): ReportExport
    {
        $export = $this->resolveExportForView($actor, $reportExportId);
        $export = $this->normalizeExpiration($export, $actor);

        if (! in_array($export->status, ['queued', 'failed'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only queued or failed report exports can be processed.'],
            ]);
        }

        $export->forceFill([
            'status' => 'processing',
            'started_at' => now(),
            'failed_at' => null,
            'last_error' => null,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.export.processing',
            actor: $actor,
            metadata: [
                'report_export_id' => $export->id,
                'report_dataset_id' => $export->report_dataset_id,
            ],
            entityType: 'report_export',
            entityId: (string) $export->id,
        );

        $requester = $export->requestedBy()->with('company')->firstOrFail();
        $dataset = $export->reportDataset()->firstOrFail();

        try {
            $result = $this->reportingQueryService->query(
                $requester,
                $dataset->key,
                $this->extractQueryPayloadFromExport($export) + ['page' => 1, 'per_page' => $this->maxExportRows()],
                false,
            );

            if ((int) ($result['meta']['total'] ?? 0) > $this->maxExportRows()) {
                throw ValidationException::withMessages([
                    'dataset_key' => ['This reporting export now exceeds the governed row limit for approved export delivery.'],
                ]);
            }

            $export = $this->finalizeCompletedExport($export, $dataset, $result, $actor);
            $this->sendCompletionNotification($requester, $export, $dataset, true, null, $actor);

            return $export;
        } catch (Throwable $exception) {
            $message = $exception instanceof ValidationException
                ? $this->flattenValidationMessage($exception)
                : 'The report export could not be generated at this time.';

            $export->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'last_error' => $message,
                'updated_by_user_id' => $actor->id,
            ])->save();

            $this->auditLogger->record(
                eventType: 'reporting.export.failed',
                actor: $actor,
                metadata: [
                    'report_export_id' => $export->id,
                    'report_dataset_id' => $export->report_dataset_id,
                    'last_error' => $message,
                ],
                entityType: 'report_export',
                entityId: (string) $export->id,
            );

            $this->sendCompletionNotification($requester, $export, $dataset, false, $message, $actor);

            return $export->load(['reportDataset', 'requestedBy']);
        }
    }

    public function downloadExport(User $actor, int $reportExportId): StreamedResponse
    {
        $export = $this->resolveExportForView($actor, $reportExportId);
        $export = $this->normalizeExpiration($export, $actor);

        $this->accessScopeService->resolveAccessibleDataset($actor, $export->reportDataset()->firstOrFail()->key);

        if ($export->status !== 'completed') {
            throw ValidationException::withMessages([
                'status' => ['Only completed report exports can be downloaded.'],
            ]);
        }

        if (! $export->disk || ! $export->file_path || ! $export->file_name) {
            throw ValidationException::withMessages([
                'export' => ['The generated report export file is no longer available.'],
            ]);
        }

        if (! Storage::disk($export->disk)->exists($export->file_path)) {
            throw ValidationException::withMessages([
                'export' => ['The generated report export file is missing from storage.'],
            ]);
        }

        $this->auditLogger->record(
            eventType: 'reporting.export.downloaded',
            actor: $actor,
            metadata: [
                'report_export_id' => $export->id,
                'report_dataset_id' => $export->report_dataset_id,
                'file_name' => $export->file_name,
                'format' => $export->format,
            ],
            entityType: 'report_export',
            entityId: (string) $export->id,
        );

        return Storage::disk($export->disk)->download(
            $export->file_path,
            $export->file_name,
            ['Content-Type' => $this->contentTypeForFormat($export->format)],
        );
    }

    private function resolveExportForView(User $actor, int $reportExportId): ReportExport
    {
        $query = ReportExport::query()
            ->with(['reportDataset', 'requestedBy'])
            ->where('company_id', $actor->company_id);

        if (! $actor->canAny(['reporting.manage', 'reporting.certify'])) {
            $query->where('requested_by_user_id', $actor->id);
        }

        return $query->findOrFail($reportExportId);
    }

    private function normalizeExpiration(ReportExport $export, User $actor): ReportExport
    {
        if ($export->status !== 'completed' || ! $export->retention_expires_at || ! $export->retention_expires_at->isPast()) {
            return $export;
        }

        $export->forceFill([
            'status' => 'expired',
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.export.expired',
            actor: $actor,
            metadata: [
                'report_export_id' => $export->id,
                'report_dataset_id' => $export->report_dataset_id,
                'retention_expires_at' => $export->retention_expires_at?->toIso8601String(),
            ],
            entityType: 'report_export',
            entityId: (string) $export->id,
        );

        return $export->refresh()->loadMissing(['reportDataset', 'requestedBy']);
    }

    /**
     * @param  ExportStorePayload  $payload
     * @return ExportQueryPayload
     */
    private function extractQueryPayload(array $payload): array
    {
        return Arr::where(
            Arr::only($payload, ['filters', 'filter_operators', 'sort_by', 'sort_direction', 'drilldown_path']),
            fn (mixed $value): bool => $value !== null,
        );
    }

    /**
     * @return ExportQueryPayload
     */
    private function extractQueryPayloadFromExport(ReportExport $export): array
    {
        return Arr::where([
            'filters' => $export->requested_filters ?? [],
            'filter_operators' => $export->requested_filter_operators ?? [],
            'sort_by' => $export->sort_by,
            'sort_direction' => $export->sort_direction,
            'drilldown_path' => $export->drilldown_path,
        ], fn (mixed $value): bool => $value !== null);
    }

    private function finalizeCompletedExport(ReportExport $export, ReportDataset $dataset, array $result, User $actor): ReportExport
    {
        $content = $this->renderExportContent($dataset, $export->format, $result);
        $disk = (string) config('reporting.exports.disk', 'local');
        $extension = $export->format === 'json' ? 'json' : 'csv';
        $fileName = sprintf('%s-%s.%s', $dataset->key, $export->export_uuid, $extension);
        $path = sprintf('companies/%d/reporting/exports/%s', $export->company_id, $fileName);

        Storage::disk($disk)->put($path, $content);

        $export->forceFill([
            'status' => 'completed',
            'execution_mode' => $export->execution_mode === 'async' ? 'async' : 'sync',
            'disk' => $disk,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_size_bytes' => strlen($content),
            'checksum_sha256' => hash('sha256', $content),
            'exported_row_count' => count($result['items'] ?? []),
            'visibility_posture' => $result['visibility'] ?? [],
            'freshness_snapshot' => $result['freshness'] ?? [],
            'started_at' => $export->started_at ?? now(),
            'completed_at' => now(),
            'failed_at' => null,
            'retention_expires_at' => now()->addHours($this->retentionHours()),
            'last_error' => null,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.export.completed',
            actor: $actor,
            metadata: [
                'report_export_id' => $export->id,
                'report_dataset_id' => $dataset->id,
                'format' => $export->format,
                'exported_row_count' => $export->exported_row_count,
                'file_name' => $fileName,
            ],
            entityType: 'report_export',
            entityId: (string) $export->id,
        );

        return $export->refresh()->load(['reportDataset', 'requestedBy']);
    }

    private function renderExportContent(ReportDataset $dataset, string $format, array $result): string
    {
        return $format === 'json'
            ? $this->renderJsonExportContent($dataset, $result)
            : $this->renderCsvExportContent($dataset, $result);
    }

    private function renderCsvExportContent(ReportDataset $dataset, array $result): string
    {
        $hiddenFieldKeys = collect($result['visibility']['hidden_field_keys'] ?? [])
            ->filter(fn (mixed $value): bool => is_string($value))
            ->values()
            ->all();
        $fieldDefinitions = collect($dataset->approved_fields ?? [])
            ->filter(fn (array $field): bool => ! in_array($field['key'] ?? '', $hiddenFieldKeys, true))
            ->values();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, $fieldDefinitions->map(fn (array $field): string => (string) ($field['label'] ?? $field['key']))->all());

        foreach ($result['items'] ?? [] as $item) {
            $row = $fieldDefinitions->map(
                fn (array $field): string => $this->stringifyExportValue($item[$field['key']] ?? null)
            )->all();

            fputcsv($stream, $row);
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $content;
    }

    private function renderJsonExportContent(ReportDataset $dataset, array $result): string
    {
        $items = collect($result['items'] ?? [])
            ->map(fn (array $item): array => Arr::except($item, ['drilldowns']))
            ->values()
            ->all();

        return json_encode([
            'dataset' => [
                'key' => $dataset->key,
                'name' => $dataset->name,
                'domain' => $dataset->domain,
                'grain' => $dataset->grain,
            ],
            'filters' => $result['filters'] ?? ['available' => [], 'applied' => []],
            'meta' => [
                'exported_row_count' => count($items),
                'sort_by' => $result['meta']['sort_by'] ?? null,
                'sort_direction' => $result['meta']['sort_direction'] ?? null,
                'drilldown_path' => $result['meta']['drilldown_path'] ?? null,
            ],
            'freshness' => $result['freshness'] ?? ['generated_at' => Carbon::now()->toIso8601String()],
            'visibility' => $result['visibility'] ?? [
                'masked_field_keys' => [],
                'hidden_field_keys' => [],
                'drilldown_keys' => [],
            ],
            'items' => $items,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function stringifyExportValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES) ?: '';
    }

    private function sendCompletionNotification(
        User $requester,
        ReportExport $export,
        ReportDataset $dataset,
        bool $successful,
        ?string $lastError,
        User $actor,
    ): void {
        $payload = $successful
            ? [
                'type' => 'reporting',
                'channel' => 'in_app',
                'title' => 'Report export is ready',
                'message' => sprintf('Your %s export is ready for download.', $dataset->name),
                'priority' => 'normal',
                'deep_link' => '/reporting/exports/'.$export->id,
                'data' => [
                    'report_export_id' => $export->id,
                    'dataset_key' => $dataset->key,
                    'status' => $export->status,
                ],
            ]
            : [
                'type' => 'reporting',
                'channel' => 'in_app',
                'title' => 'Report export failed',
                'message' => $lastError
                    ? sprintf('Your %s export failed: %s', $dataset->name, $lastError)
                    : sprintf('Your %s export could not be completed.', $dataset->name),
                'priority' => 'high',
                'deep_link' => '/reporting/exports/'.$export->id,
                'data' => [
                    'report_export_id' => $export->id,
                    'dataset_key' => $dataset->key,
                    'status' => $export->status,
                    'last_error' => $lastError,
                ],
            ];

        $this->notificationService->sendDirect($requester, $payload, $actor);

        $export->forceFill([
            'notified_at' => now(),
            'updated_by_user_id' => $actor->id,
        ])->save();
    }

    private function flattenValidationMessage(ValidationException $exception): string
    {
        foreach ($exception->errors() as $messages) {
            if (is_array($messages) && $messages !== []) {
                return (string) $messages[0];
            }
        }

        return $exception->getMessage();
    }

    private function contentTypeForFormat(string $format): string
    {
        return match ($format) {
            'json' => 'application/json; charset=UTF-8',
            default => 'text/csv; charset=UTF-8',
        };
    }

    private function syncRowLimit(): int
    {
        return (int) config('reporting.exports.sync_row_limit', 500);
    }

    private function maxExportRows(): int
    {
        return (int) config('reporting.exports.max_row_limit', 5000);
    }

    private function retentionHours(): int
    {
        return (int) config('reporting.exports.retention_hours', 48);
    }
}
