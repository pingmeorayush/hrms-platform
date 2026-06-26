<?php

namespace App\Modules\ReportingAnalytics\Services;

use App\Models\ReportSubscription;
use App\Models\SavedReportView;
use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * @phpstan-type SubscriptionPayload array{
 *   dataset_key?: string,
 *   saved_report_view_id?: int,
 *   name?: string,
 *   description?: string|null,
 *   status?: string,
 *   delivery_channel?: string,
 *   delivery_target?: string,
 *   export_format?: string,
 *   frequency?: string,
 *   timezone?: string,
 *   schedule_config?: array<string, mixed>,
 *   filters?: array<string, mixed>,
 *   filter_operators?: array<string, string>,
 *   sort_by?: string,
 *   sort_direction?: string,
 *   drilldown_path?: string
 * }
 */
class ReportingSubscriptionService
{
    public function __construct(
        private readonly ReportingSavedViewService $savedViewService,
        private readonly ReportingAccessScopeService $accessScopeService,
        private readonly ReportingQueryService $reportingQueryService,
        private readonly ReportingExportService $reportingExportService,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function searchSubscriptions(User $actor, array $filters): LengthAwarePaginator
    {
        $query = ReportSubscription::query()
            ->with(['reportDataset', 'savedReportView', 'owner', 'lastReportExport', 'createdBy', 'updatedBy'])
            ->where('company_id', $actor->company_id)
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if (! $this->canManageAllSubscriptions($actor) || (bool) ($filters['owned_by_me'] ?? false)) {
            $query->where('owner_user_id', $actor->id);
        }

        if (is_string($filters['status'] ?? null) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (is_string($filters['frequency'] ?? null) && $filters['frequency'] !== '') {
            $query->where('frequency', $filters['frequency']);
        }

        if (is_string($filters['dataset_key'] ?? null) && $filters['dataset_key'] !== '') {
            $query->whereHas('reportDataset', fn ($builder) => $builder->where('key', $filters['dataset_key']));
        }

        $perPage = is_numeric($filters['per_page'] ?? null) ? (int) $filters['per_page'] : 20;
        $subscriptions = $query->paginate($perPage);
        $subscriptions->setCollection(
            $subscriptions->getCollection()->map(fn (ReportSubscription $subscription): ReportSubscription => $this->attachValidationState($subscription))
        );

        $this->auditLogger->record(
            eventType: 'reporting.subscription.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'result_count' => count($subscriptions->items()),
            ],
            entityType: 'report_subscription',
            entityId: null,
        );

        return $subscriptions;
    }

    /**
     * @param  SubscriptionPayload  $payload
     */
    public function createSubscription(User $actor, array $payload): ReportSubscription
    {
        $source = $this->resolveSourceConfiguration($actor, $payload);
        $normalized = $this->normalizeSubscriptionPayload($payload, $actor);

        $subscription = ReportSubscription::query()->create([
            'company_id' => $actor->company_id,
            'report_dataset_id' => $source['report_dataset_id'],
            'saved_report_view_id' => $source['saved_report_view_id'],
            'owner_user_id' => $actor->id,
            'subscription_uuid' => (string) Str::uuid(),
            'name' => $normalized['name'],
            'description' => $normalized['description'],
            'status' => $normalized['status'],
            'delivery_channel' => $normalized['delivery_channel'],
            'delivery_target' => $normalized['delivery_target'],
            'export_format' => $normalized['export_format'],
            'frequency' => $normalized['frequency'],
            'timezone' => $normalized['timezone'],
            'schedule_config' => $normalized['schedule_config'],
            'filters' => $source['filters'],
            'filter_operators' => $source['filter_operators'],
            'sort_by' => $source['sort_by'],
            'sort_direction' => $source['sort_direction'],
            'drilldown_path' => $source['drilldown_path'],
            'next_delivery_at' => $this->calculateNextDeliveryAt(
                $normalized['frequency'],
                $normalized['timezone'],
                $normalized['schedule_config'],
            ),
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);

        $this->auditLogger->record(
            eventType: 'reporting.subscription.created',
            actor: $actor,
            metadata: [
                'report_subscription_id' => $subscription->id,
                'report_dataset_id' => $subscription->report_dataset_id,
                'saved_report_view_id' => $subscription->saved_report_view_id,
                'frequency' => $subscription->frequency,
                'delivery_channel' => $subscription->delivery_channel,
            ],
            entityType: 'report_subscription',
            entityId: (string) $subscription->id,
        );

        return $this->attachValidationState(
            $subscription->load(['reportDataset', 'savedReportView', 'owner', 'lastReportExport', 'createdBy', 'updatedBy'])
        );
    }

    public function showSubscription(User $actor, int $reportSubscriptionId): ReportSubscription
    {
        $subscription = $this->findAccessibleSubscription($actor, $reportSubscriptionId);
        $subscription = $this->attachValidationState($subscription);

        $this->auditLogger->record(
            eventType: 'reporting.subscription.viewed',
            actor: $actor,
            metadata: [
                'report_subscription_id' => $subscription->id,
                'status' => $subscription->status,
            ],
            entityType: 'report_subscription',
            entityId: (string) $subscription->id,
        );

        return $subscription;
    }

    /**
     * @param  SubscriptionPayload  $payload
     */
    public function updateSubscription(User $actor, int $reportSubscriptionId, array $payload): ReportSubscription
    {
        $subscription = $this->findAccessibleSubscription($actor, $reportSubscriptionId);
        $owner = $subscription->owner()->with('company')->firstOrFail();
        $normalized = $this->normalizeSubscriptionPayload($payload, $owner, $subscription);
        $source = $this->resolveSourceConfiguration($owner, $payload, $subscription);

        $subscription->forceFill([
            'report_dataset_id' => $source['report_dataset_id'],
            'saved_report_view_id' => $source['saved_report_view_id'],
            'name' => $normalized['name'],
            'description' => $normalized['description'],
            'status' => $normalized['status'],
            'delivery_channel' => $normalized['delivery_channel'],
            'delivery_target' => $normalized['delivery_target'],
            'export_format' => $normalized['export_format'],
            'frequency' => $normalized['frequency'],
            'timezone' => $normalized['timezone'],
            'schedule_config' => $normalized['schedule_config'],
            'filters' => $source['filters'],
            'filter_operators' => $source['filter_operators'],
            'sort_by' => $source['sort_by'],
            'sort_direction' => $source['sort_direction'],
            'drilldown_path' => $source['drilldown_path'],
            'next_delivery_at' => in_array($normalized['status'], ['active', 'blocked'], true)
                ? $this->calculateNextDeliveryAt($normalized['frequency'], $normalized['timezone'], $normalized['schedule_config'])
                : null,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.subscription.updated',
            actor: $actor,
            metadata: [
                'report_subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'frequency' => $subscription->frequency,
            ],
            entityType: 'report_subscription',
            entityId: (string) $subscription->id,
        );

        return $this->attachValidationState(
            $subscription->refresh()->load(['reportDataset', 'savedReportView', 'owner', 'lastReportExport', 'createdBy', 'updatedBy'])
        );
    }

    public function revokeSubscription(User $actor, int $reportSubscriptionId): ReportSubscription
    {
        $subscription = $this->findAccessibleSubscription($actor, $reportSubscriptionId);

        $subscription->forceFill([
            'status' => 'revoked',
            'next_delivery_at' => null,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.subscription.revoked',
            actor: $actor,
            metadata: [
                'report_subscription_id' => $subscription->id,
            ],
            entityType: 'report_subscription',
            entityId: (string) $subscription->id,
        );

        return $this->attachValidationState(
            $subscription->refresh()->load(['reportDataset', 'savedReportView', 'owner', 'lastReportExport', 'createdBy', 'updatedBy'])
        );
    }

    public function deliverSubscription(User $actor, int $reportSubscriptionId): ReportSubscription
    {
        $subscription = $this->findAccessibleSubscription($actor, $reportSubscriptionId);

        if ($subscription->status === 'revoked') {
            throw ValidationException::withMessages([
                'status' => ['Revoked report subscriptions cannot be delivered.'],
            ]);
        }

        $validation = $this->validationStateForSubscription($subscription);

        if (($validation['status'] ?? null) !== 'valid') {
            return $this->blockSubscription($subscription, $actor, (string) ($validation['reason'] ?? 'The subscription is no longer deliverable.'));
        }

        $owner = $subscription->owner()->with('company')->firstOrFail();
        $payload = $this->buildExportPayload($subscription);
        $export = $this->reportingExportService->requestExport($owner, $payload, $actor, true);

        if ($export->status === 'queued') {
            $export = $this->reportingExportService->processExport($actor, $export->id);
        }

        if ($export->status !== 'completed') {
            return $this->blockSubscription(
                $subscription,
                $actor,
                $export->last_error ?: 'The report export could not be completed for this subscription.',
                'failed',
            );
        }

        $subscription->forceFill([
            'status' => 'active',
            'last_report_export_id' => $export->id,
            'last_delivered_at' => now(),
            'last_delivery_status' => 'completed',
            'last_delivery_error' => null,
            'next_delivery_at' => $this->calculateNextDeliveryAt(
                $subscription->frequency,
                $subscription->timezone,
                $subscription->schedule_config ?? [],
            ),
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.subscription.delivered',
            actor: $actor,
            metadata: [
                'report_subscription_id' => $subscription->id,
                'report_export_id' => $export->id,
                'report_dataset_id' => $subscription->report_dataset_id,
                'saved_report_view_id' => $subscription->saved_report_view_id,
            ],
            entityType: 'report_subscription',
            entityId: (string) $subscription->id,
        );

        return $this->attachValidationState(
            $subscription->refresh()->load(['reportDataset', 'savedReportView', 'owner', 'lastReportExport', 'createdBy', 'updatedBy'])
        );
    }

    private function findAccessibleSubscription(User $actor, int $reportSubscriptionId): ReportSubscription
    {
        $query = ReportSubscription::query()
            ->with(['reportDataset', 'savedReportView.reportDataset', 'savedReportView.owner', 'owner', 'lastReportExport', 'createdBy', 'updatedBy'])
            ->where('company_id', $actor->company_id);

        if (! $this->canManageAllSubscriptions($actor)) {
            $query->where('owner_user_id', $actor->id);
        }

        return $query->findOrFail($reportSubscriptionId);
    }

    private function canManageAllSubscriptions(User $actor): bool
    {
        return $actor->canAny(['reporting.manage', 'reporting.certify']);
    }

    private function attachValidationState(ReportSubscription $subscription): ReportSubscription
    {
        $subscription->setAttribute('validation_state', $this->validationStateForSubscription($subscription));

        return $subscription;
    }

    private function validationStateForSubscription(ReportSubscription $subscription): array
    {
        if ($subscription->status === 'revoked') {
            return [
                'status' => 'blocked',
                'reason' => 'The report subscription has been revoked.',
            ];
        }

        $owner = $subscription->owner;

        if (! $owner instanceof User) {
            return [
                'status' => 'blocked',
                'reason' => 'The subscription owner is no longer available.',
            ];
        }

        if ($subscription->savedReportView) {
            $view = $subscription->savedReportView;

            if ($view->status !== 'active') {
                return [
                    'status' => 'blocked',
                    'reason' => 'The referenced saved report view is archived.',
                ];
            }

            if (! $this->canOwnerConsumeSavedView($owner, $view)) {
                return [
                    'status' => 'blocked',
                    'reason' => 'The subscription owner no longer has access to the referenced saved report view.',
                ];
            }

            return $this->savedViewService->validationStateForActor($owner, $view);
        }

        if (! $subscription->reportDataset) {
            return [
                'status' => 'blocked',
                'reason' => 'The referenced reporting dataset is no longer available.',
            ];
        }

        try {
            $this->reportingQueryService->query(
                $owner,
                $subscription->reportDataset->key,
                $this->buildDirectQueryPayload($subscription) + ['page' => 1, 'per_page' => 1],
                false,
            );

            return [
                'status' => 'valid',
                'reason' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'blocked',
                'reason' => $this->flattenExceptionMessage($exception),
            ];
        }
    }

    /**
     * @param  SubscriptionPayload  $payload
     * @return array{
     *   report_dataset_id: int|null,
     *   saved_report_view_id: int|null,
     *   filters: array<string, mixed>|null,
     *   filter_operators: array<string, string>|null,
     *   sort_by: string|null,
     *   sort_direction: string|null,
     *   drilldown_path: string|null
     * }
     */
    private function resolveSourceConfiguration(User $actor, array $payload, ?ReportSubscription $existing = null): array
    {
        $savedReportViewId = array_key_exists('saved_report_view_id', $payload)
            ? $payload['saved_report_view_id']
            : (! array_key_exists('dataset_key', $payload) ? $existing?->saved_report_view_id : null);
        $datasetKey = array_key_exists('dataset_key', $payload)
            ? $payload['dataset_key']
            : (! array_key_exists('saved_report_view_id', $payload) ? $existing?->reportDataset?->key : null);

        if ($savedReportViewId && $datasetKey) {
            throw ValidationException::withMessages([
                'saved_report_view_id' => ['Subscriptions must reference either a saved report view or a direct dataset, not both.'],
            ]);
        }

        if ($savedReportViewId) {
            /** @var SavedReportView $view */
            $view = SavedReportView::query()
                ->with(['reportDataset', 'owner'])
                ->where('company_id', $actor->company_id)
                ->findOrFail((int) $savedReportViewId);

            if (! $this->canOwnerConsumeSavedView($actor, $view)) {
                throw new AuthorizationException('You are not allowed to subscribe to this saved report view.');
            }

            $validation = $this->savedViewService->validationStateForActor($actor, $view);

            if (($validation['status'] ?? null) !== 'valid') {
                throw ValidationException::withMessages([
                    'saved_report_view_id' => [$validation['reason'] ?? 'The saved report view is no longer valid for subscription delivery.'],
                ]);
            }

            return [
                'report_dataset_id' => $view->report_dataset_id,
                'saved_report_view_id' => $view->id,
                'filters' => null,
                'filter_operators' => null,
                'sort_by' => null,
                'sort_direction' => null,
                'drilldown_path' => null,
            ];
        }

        if ($datasetKey === null && $existing?->reportDataset) {
            $datasetKey = $existing->reportDataset->key;
        }

        if (! is_string($datasetKey) || $datasetKey === '') {
            throw ValidationException::withMessages([
                'dataset_key' => ['A subscription must reference either a saved report view or a direct reporting dataset.'],
            ]);
        }

        $dataset = $this->accessScopeService->resolveAccessibleDataset($actor, $datasetKey);
        $queryPayload = [
            'filters' => $payload['filters'] ?? $existing?->filters ?? [],
            'filter_operators' => $payload['filter_operators'] ?? $existing?->filter_operators ?? [],
            'sort_by' => $payload['sort_by'] ?? $existing?->sort_by,
            'sort_direction' => $payload['sort_direction'] ?? $existing?->sort_direction,
            'drilldown_path' => $payload['drilldown_path'] ?? $existing?->drilldown_path,
        ];

        $this->reportingQueryService->query(
            $actor,
            $dataset->key,
            Arr::where($queryPayload, fn (mixed $value): bool => $value !== null) + ['page' => 1, 'per_page' => 1],
            false,
        );

        return [
            'report_dataset_id' => $dataset->id,
            'saved_report_view_id' => null,
            'filters' => $queryPayload['filters'],
            'filter_operators' => $queryPayload['filter_operators'],
            'sort_by' => $queryPayload['sort_by'],
            'sort_direction' => $queryPayload['sort_direction'],
            'drilldown_path' => $queryPayload['drilldown_path'],
        ];
    }

    /**
     * @param  SubscriptionPayload  $payload
     * @return array{
     *   name: string,
     *   description: string|null,
     *   status: string,
     *   delivery_channel: string,
     *   delivery_target: string,
     *   export_format: string,
     *   frequency: string,
     *   timezone: string,
     *   schedule_config: array<string, mixed>
     * }
     */
    private function normalizeSubscriptionPayload(array $payload, User $owner, ?ReportSubscription $existing = null): array
    {
        $status = $payload['status'] ?? $existing?->status ?? 'active';
        $frequency = $payload['frequency'] ?? $existing?->frequency;
        $timezone = $payload['timezone'] ?? $existing?->timezone ?? ($owner->company->timezone ?? 'UTC');
        $scheduleConfig = $payload['schedule_config'] ?? $existing?->schedule_config;

        if (! is_array($scheduleConfig) || $scheduleConfig === []) {
            throw ValidationException::withMessages([
                'schedule_config' => ['A delivery schedule is required for the report subscription.'],
            ]);
        }

        $this->assertScheduleConfigMatchesFrequency((string) $frequency, $scheduleConfig);

        return [
            'name' => (string) ($payload['name'] ?? $existing?->name),
            'description' => array_key_exists('description', $payload) ? $payload['description'] : $existing?->description,
            'status' => (string) $status,
            'delivery_channel' => (string) ($payload['delivery_channel'] ?? $existing?->delivery_channel ?? 'in_app_notification'),
            'delivery_target' => (string) ($payload['delivery_target'] ?? $existing?->delivery_target ?? 'owner_only'),
            'export_format' => (string) ($payload['export_format'] ?? $existing?->export_format ?? 'csv'),
            'frequency' => (string) $frequency,
            'timezone' => (string) $timezone,
            'schedule_config' => $scheduleConfig,
        ];
    }

    private function assertScheduleConfigMatchesFrequency(string $frequency, array $scheduleConfig): void
    {
        if (! is_string($scheduleConfig['time_of_day'] ?? null)) {
            throw ValidationException::withMessages([
                'schedule_config.time_of_day' => ['A `time_of_day` value is required for the subscription schedule.'],
            ]);
        }

        if ($frequency === 'weekly' && ! is_int($scheduleConfig['weekday'] ?? null)) {
            throw ValidationException::withMessages([
                'schedule_config.weekday' => ['A `weekday` value is required for weekly report subscriptions.'],
            ]);
        }

        if ($frequency === 'monthly' && ! is_int($scheduleConfig['day_of_month'] ?? null)) {
            throw ValidationException::withMessages([
                'schedule_config.day_of_month' => ['A `day_of_month` value is required for monthly report subscriptions.'],
            ]);
        }
    }

    private function canOwnerConsumeSavedView(User $owner, SavedReportView $view): bool
    {
        if ($view->owner_user_id === $owner->id) {
            return true;
        }

        if ($view->status !== 'active') {
            return false;
        }

        if ($view->share_scope === 'company') {
            return true;
        }

        if ($view->share_scope !== 'roles') {
            return false;
        }

        $ownerRoleNames = $owner->getRoleNames()->all();
        $sharedRoleNames = collect($view->shared_role_names ?? [])
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->values()
            ->all();

        return array_intersect($ownerRoleNames, $sharedRoleNames) !== [];
    }

    private function buildExportPayload(ReportSubscription $subscription): array
    {
        if ($subscription->savedReportView) {
            return [
                'dataset_key' => $subscription->savedReportView->reportDataset->key,
                'format' => $subscription->export_format,
                'execution_mode' => 'auto',
                'delivery_target' => 'requestor_download',
                'filters' => $subscription->savedReportView->filters ?? [],
                'filter_operators' => $subscription->savedReportView->filter_operators ?? [],
                'sort_by' => $subscription->savedReportView->sort_by,
                'sort_direction' => $subscription->savedReportView->sort_direction,
                'drilldown_path' => $subscription->savedReportView->drilldown_path,
            ];
        }

        return [
            'dataset_key' => $subscription->reportDataset->key,
            'format' => $subscription->export_format,
            'execution_mode' => 'auto',
            'delivery_target' => 'requestor_download',
            'filters' => $subscription->filters ?? [],
            'filter_operators' => $subscription->filter_operators ?? [],
            'sort_by' => $subscription->sort_by,
            'sort_direction' => $subscription->sort_direction,
            'drilldown_path' => $subscription->drilldown_path,
        ];
    }

    private function buildDirectQueryPayload(ReportSubscription $subscription): array
    {
        return Arr::where([
            'filters' => $subscription->filters ?? [],
            'filter_operators' => $subscription->filter_operators ?? [],
            'sort_by' => $subscription->sort_by,
            'sort_direction' => $subscription->sort_direction,
            'drilldown_path' => $subscription->drilldown_path,
        ], fn (mixed $value): bool => $value !== null);
    }

    private function calculateNextDeliveryAt(string $frequency, string $timezone, array $scheduleConfig): Carbon
    {
        [$hour, $minute] = array_map('intval', explode(':', (string) $scheduleConfig['time_of_day']));
        $now = Carbon::now($timezone)->setSecond(0);
        $candidate = $now->copy()->setTime($hour, $minute);

        $nextDeliveryAt = match ($frequency) {
            'weekly' => $this->nextWeeklyDeliveryAt($candidate, $now, (int) $scheduleConfig['weekday']),
            'monthly' => $this->nextMonthlyDeliveryAt($candidate, $now, (int) $scheduleConfig['day_of_month']),
            default => $this->nextDailyDeliveryAt($candidate, $now),
        };

        return $nextDeliveryAt->utc();
    }

    private function nextDailyDeliveryAt(Carbon $candidate, Carbon $now): Carbon
    {
        if ($candidate->lessThanOrEqualTo($now)) {
            return $candidate->addDay();
        }

        return $candidate;
    }

    private function nextWeeklyDeliveryAt(Carbon $candidate, Carbon $now, int $weekday): Carbon
    {
        $currentWeekday = $candidate->dayOfWeek;
        $offset = ($weekday - $currentWeekday + 7) % 7;
        $candidate = $candidate->copy()->addDays($offset);

        if ($candidate->lessThanOrEqualTo($now)) {
            $candidate->addWeek();
        }

        return $candidate;
    }

    private function nextMonthlyDeliveryAt(Carbon $candidate, Carbon $now, int $dayOfMonth): Carbon
    {
        $candidate = $candidate->copy()->day($dayOfMonth);

        if ($candidate->lessThanOrEqualTo($now)) {
            $candidate->addMonthNoOverflow()->day($dayOfMonth);
        }

        return $candidate;
    }

    private function blockSubscription(
        ReportSubscription $subscription,
        User $actor,
        string $reason,
        string $deliveryStatus = 'blocked',
    ): ReportSubscription {
        $subscription->forceFill([
            'status' => 'blocked',
            'last_delivery_status' => $deliveryStatus,
            'last_delivery_error' => $reason,
            'next_delivery_at' => null,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'reporting.subscription.blocked',
            actor: $actor,
            metadata: [
                'report_subscription_id' => $subscription->id,
                'reason' => $reason,
                'last_delivery_status' => $deliveryStatus,
            ],
            entityType: 'report_subscription',
            entityId: (string) $subscription->id,
        );

        return $this->attachValidationState(
            $subscription->refresh()->load(['reportDataset', 'savedReportView', 'owner', 'lastReportExport', 'createdBy', 'updatedBy'])
        );
    }

    private function flattenExceptionMessage(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            foreach ($exception->errors() as $messages) {
                if (is_array($messages) && $messages !== []) {
                    return (string) $messages[0];
                }
            }
        }

        return $exception->getMessage();
    }
}
