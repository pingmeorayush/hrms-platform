<?php

namespace App\Modules\IntegrationsPlatform\Services;

use App\Models\Company;
use App\Models\IntegrationConnection;
use App\Models\IntegrationSyncError;
use App\Models\IntegrationSyncJob;
use App\Models\User;
use App\Models\WebhookSubscription;
use App\Modules\Platform\Audit\Services\AuditLogger;
use App\Modules\Platform\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class IntegrationPlatformService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @return array{
     *   systems: array<int, array<string, mixed>>,
     *   events: array<int, array<string, mixed>>
     * }
     */
    public function catalog(User $actor): array
    {
        $payload = [
            'systems' => array_values(config('integrations.systems', [])),
            'events' => array_values(config('integrations.events', [])),
        ];

        $this->auditLogger->record(
            eventType: 'integrations.catalog.viewed',
            actor: $actor,
            metadata: [
                'system_count' => count($payload['systems']),
                'event_count' => count($payload['events']),
            ],
            entityType: 'integration_catalog',
            entityId: null,
        );

        return $payload;
    }

    /**
     * @return Collection<int, IntegrationConnection>
     */
    public function listConnections(User $actor, array $filters): Collection
    {
        $query = IntegrationConnection::query()
            ->where('company_id', $actor->company_id)
            ->withCount([
                'webhookSubscriptions as active_subscription_count' => fn (Builder $builder): Builder => $builder->where('status', 'active'),
            ]);

        if (is_string($filters['system_key'] ?? null) && $filters['system_key'] !== '') {
            $query->where('system_key', $filters['system_key']);
        }

        if (is_string($filters['status'] ?? null) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (is_string($filters['direction'] ?? null) && $filters['direction'] !== '') {
            $query->where('direction', $filters['direction']);
        }

        $connections = $query
            ->orderBy('system_key')
            ->orderBy('name')
            ->get();

        $this->auditLogger->record(
            eventType: 'integrations.connection.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'result_count' => $connections->count(),
            ],
            entityType: 'integration_connection',
            entityId: null,
        );

        return $connections;
    }

    public function createConnection(User $actor, array $payload): IntegrationConnection
    {
        $connection = IntegrationConnection::query()->create([
            'company_id' => $actor->company_id,
            'system_key' => $payload['system_key'],
            'version' => is_string($payload['version'] ?? null) ? $payload['version'] : 'v1',
            'name' => trim((string) $payload['name']),
            'direction' => $payload['direction'],
            'status' => is_string($payload['status'] ?? null) ? $payload['status'] : 'draft',
            'auth_mode' => is_string($payload['auth_mode'] ?? null) ? $payload['auth_mode'] : 'hmac_sha256',
            'endpoint_url' => $this->nullableString($payload['endpoint_url'] ?? null),
            'description' => $this->nullableString($payload['description'] ?? null),
            'scopes' => Arr::wrap($payload['scopes'] ?? []),
            'settings' => is_array($payload['settings'] ?? null) ? $payload['settings'] : [],
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);

        $this->auditLogger->record(
            eventType: 'integrations.connection.created',
            actor: $actor,
            metadata: [
                'integration_connection_id' => $connection->id,
                'system_key' => $connection->system_key,
                'direction' => $connection->direction,
                'status' => $connection->status,
            ],
            entityType: 'integration_connection',
            entityId: (string) $connection->id,
        );

        return $connection->loadCount([
            'webhookSubscriptions as active_subscription_count' => fn (Builder $builder): Builder => $builder->where('status', 'active'),
        ]);
    }

    public function updateConnection(User $actor, int $connectionId, array $payload): IntegrationConnection
    {
        $connection = $this->resolveConnection($actor, $connectionId);

        $connection->forceFill([
            'name' => array_key_exists('name', $payload) ? trim((string) $payload['name']) : $connection->name,
            'version' => is_string($payload['version'] ?? null) ? $payload['version'] : $connection->version,
            'direction' => is_string($payload['direction'] ?? null) ? $payload['direction'] : $connection->direction,
            'status' => is_string($payload['status'] ?? null) ? $payload['status'] : $connection->status,
            'auth_mode' => is_string($payload['auth_mode'] ?? null) ? $payload['auth_mode'] : $connection->auth_mode,
            'endpoint_url' => array_key_exists('endpoint_url', $payload)
                ? $this->nullableString($payload['endpoint_url'])
                : $connection->endpoint_url,
            'description' => array_key_exists('description', $payload)
                ? $this->nullableString($payload['description'])
                : $connection->description,
            'scopes' => array_key_exists('scopes', $payload) ? Arr::wrap($payload['scopes']) : $connection->scopes,
            'settings' => is_array($payload['settings'] ?? null) ? $payload['settings'] : $connection->settings,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'integrations.connection.updated',
            actor: $actor,
            metadata: [
                'integration_connection_id' => $connection->id,
                'system_key' => $connection->system_key,
                'status' => $connection->status,
            ],
            entityType: 'integration_connection',
            entityId: (string) $connection->id,
        );

        return $connection->loadCount([
            'webhookSubscriptions as active_subscription_count' => fn (Builder $builder): Builder => $builder->where('status', 'active'),
        ]);
    }

    /**
     * @return Collection<int, WebhookSubscription>
     */
    public function listWebhookSubscriptions(User $actor, array $filters): Collection
    {
        $query = WebhookSubscription::query()
            ->with('connection')
            ->where('company_id', $actor->company_id);

        if (is_numeric($filters['integration_connection_id'] ?? null)) {
            $query->where('integration_connection_id', (int) $filters['integration_connection_id']);
        }

        if (is_string($filters['event_key'] ?? null) && $filters['event_key'] !== '') {
            $query->where('event_key', $filters['event_key']);
        }

        if (is_string($filters['status'] ?? null) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (is_string($filters['direction'] ?? null) && $filters['direction'] !== '') {
            $query->where('direction', $filters['direction']);
        }

        $subscriptions = $query
            ->orderBy('event_key')
            ->orderBy('id')
            ->get();

        $this->auditLogger->record(
            eventType: 'integrations.subscription.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'result_count' => $subscriptions->count(),
            ],
            entityType: 'webhook_subscription',
            entityId: null,
        );

        return $subscriptions;
    }

    public function createWebhookSubscription(User $actor, array $payload): WebhookSubscription
    {
        $connection = $this->resolveConnection($actor, (int) $payload['integration_connection_id']);
        $direction = (string) $payload['direction'];
        $eventKey = (string) $payload['event_key'];

        $this->ensureConnectionDirectionSupportsSubscription($connection->direction, $direction);
        $this->ensureEventSupportsDirection($eventKey, $direction);
        $this->ensureEventSupportsSystem($eventKey, $connection->system_key);

        $subscription = WebhookSubscription::query()->create([
            'company_id' => $actor->company_id,
            'integration_connection_id' => $connection->id,
            'subscription_key' => (string) Str::uuid(),
            'version' => is_string($payload['version'] ?? null) ? $payload['version'] : 'v1',
            'event_key' => $eventKey,
            'direction' => $direction,
            'status' => is_string($payload['status'] ?? null) ? $payload['status'] : 'active',
            'endpoint_url' => $this->nullableString($payload['endpoint_url'] ?? null),
            'secret' => (string) $payload['secret'],
            'custom_headers' => is_array($payload['custom_headers'] ?? null) ? $payload['custom_headers'] : [],
            'filter_rules' => is_array($payload['filter_rules'] ?? null) ? $payload['filter_rules'] : [],
            'created_by_user_id' => $actor->id,
            'updated_by_user_id' => $actor->id,
        ]);

        $this->auditLogger->record(
            eventType: 'integrations.subscription.created',
            actor: $actor,
            metadata: [
                'webhook_subscription_id' => $subscription->id,
                'integration_connection_id' => $connection->id,
                'event_key' => $subscription->event_key,
                'direction' => $subscription->direction,
                'status' => $subscription->status,
            ],
            entityType: 'webhook_subscription',
            entityId: (string) $subscription->id,
        );

        return $subscription->load('connection');
    }

    public function updateWebhookSubscription(User $actor, int $subscriptionId, array $payload): WebhookSubscription
    {
        $subscription = $this->resolveSubscription($actor, $subscriptionId);
        $connection = $subscription->connection()->firstOrFail();
        $direction = is_string($payload['direction'] ?? null) ? $payload['direction'] : $subscription->direction;
        $eventKey = is_string($payload['event_key'] ?? null) ? $payload['event_key'] : $subscription->event_key;

        $this->ensureConnectionDirectionSupportsSubscription($connection->direction, $direction);
        $this->ensureEventSupportsDirection($eventKey, $direction);
        $this->ensureEventSupportsSystem($eventKey, $connection->system_key);

        $subscription->forceFill([
            'version' => is_string($payload['version'] ?? null) ? $payload['version'] : $subscription->version,
            'event_key' => $eventKey,
            'direction' => $direction,
            'status' => is_string($payload['status'] ?? null) ? $payload['status'] : $subscription->status,
            'endpoint_url' => array_key_exists('endpoint_url', $payload)
                ? $this->nullableString($payload['endpoint_url'])
                : $subscription->endpoint_url,
            'secret' => is_string($payload['secret'] ?? null) ? $payload['secret'] : $subscription->secret,
            'custom_headers' => is_array($payload['custom_headers'] ?? null)
                ? $payload['custom_headers']
                : $subscription->custom_headers,
            'filter_rules' => is_array($payload['filter_rules'] ?? null)
                ? $payload['filter_rules']
                : $subscription->filter_rules,
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'integrations.subscription.updated',
            actor: $actor,
            metadata: [
                'webhook_subscription_id' => $subscription->id,
                'integration_connection_id' => $connection->id,
                'event_key' => $subscription->event_key,
                'status' => $subscription->status,
            ],
            entityType: 'webhook_subscription',
            entityId: (string) $subscription->id,
        );

        return $subscription->load('connection');
    }

    /**
     * @return Collection<int, IntegrationSyncJob>
     */
    public function dispatchEvent(User $actor, array $payload): Collection
    {
        $eventKey = (string) $payload['event_key'];
        $this->ensureEventSupportsDirection($eventKey, 'outbound');

        $query = WebhookSubscription::query()
            ->with('connection')
            ->where('company_id', $actor->company_id)
            ->where('status', 'active')
            ->where('direction', 'outbound')
            ->where('event_key', $eventKey)
            ->whereHas('connection', fn (Builder $builder): Builder => $builder->where('status', 'active'));

        if (is_array($payload['subscription_ids'] ?? null) && $payload['subscription_ids'] !== []) {
            $query->whereIn('id', array_map('intval', $payload['subscription_ids']));
        }

        $subscriptions = $query
            ->get()
            ->filter(fn (WebhookSubscription $subscription): bool => $this->subscriptionMatchesDispatch($subscription, $payload));

        if ($subscriptions->isEmpty()) {
            throw ValidationException::withMessages([
                'event_key' => ['No active outbound subscription matched the requested event dispatch.'],
            ]);
        }

        $processNow = (bool) ($payload['process_now'] ?? true);

        $jobs = $subscriptions->map(function (WebhookSubscription $subscription) use ($actor, $payload, $processNow): IntegrationSyncJob {
            $job = $this->queueJobForSubscription(
                subscription: $subscription,
                direction: 'outbound',
                triggerSource: 'manual_event',
                requestPayload: is_array($payload['payload']) ? $payload['payload'] : [],
                entityType: $this->nullableString($payload['entity_type'] ?? null),
                entityId: $this->nullableString($payload['entity_id'] ?? null),
                actor: $actor,
            );

            return $processNow
                ? $this->executeJob($job, $actor)
                : $job->load(['connection', 'subscription', 'errors']);
        });

        $this->auditLogger->record(
            eventType: 'integrations.event.dispatched',
            actor: $actor,
            metadata: [
                'event_key' => $eventKey,
                'job_count' => $jobs->count(),
                'process_now' => $processNow,
                'subscription_ids' => $subscriptions->pluck('id')->all(),
            ],
            entityType: 'integration_sync_job',
            entityId: null,
        );

        return $jobs;
    }

    public function listSyncJobs(User $actor, array $filters): LengthAwarePaginator
    {
        $query = IntegrationSyncJob::query()
            ->with(['connection', 'subscription', 'errors'])
            ->where('company_id', $actor->company_id);

        if (is_string($filters['status'] ?? null) && $filters['status'] !== '') {
            if ($filters['status'] === 'retried') {
                $query->where('status', 'completed')->whereNotNull('retried_at');
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (is_string($filters['event_key'] ?? null) && $filters['event_key'] !== '') {
            $query->where('event_key', $filters['event_key']);
        }

        if (is_numeric($filters['integration_connection_id'] ?? null)) {
            $query->where('integration_connection_id', (int) $filters['integration_connection_id']);
        }

        if (is_numeric($filters['webhook_subscription_id'] ?? null)) {
            $query->where('webhook_subscription_id', (int) $filters['webhook_subscription_id']);
        }

        $perPage = is_numeric($filters['per_page'] ?? null) ? (int) $filters['per_page'] : 25;
        $jobs = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        $this->auditLogger->record(
            eventType: 'integrations.sync-job.listed',
            actor: $actor,
            metadata: [
                'filters' => $filters,
                'result_count' => count($jobs->items()),
            ],
            entityType: 'integration_sync_job',
            entityId: null,
        );

        return $jobs;
    }

    public function showSyncJob(User $actor, int $jobId): IntegrationSyncJob
    {
        $job = $this->resolveSyncJob($actor, $jobId);

        $this->auditLogger->record(
            eventType: 'integrations.sync-job.viewed',
            actor: $actor,
            metadata: [
                'integration_sync_job_id' => $job->id,
                'status' => $job->status,
                'event_key' => $job->event_key,
            ],
            entityType: 'integration_sync_job',
            entityId: (string) $job->id,
        );

        return $job;
    }

    public function processSyncJob(User $actor, int $jobId): IntegrationSyncJob
    {
        $job = $this->resolveSyncJob($actor, $jobId);

        if (! in_array($job->status, ['queued', 'failed'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only queued or failed sync jobs can be processed.'],
            ]);
        }

        return $this->executeJob($job, $actor);
    }

    public function retrySyncJob(User $actor, int $jobId): IntegrationSyncJob
    {
        $job = $this->resolveSyncJob($actor, $jobId);

        if ($job->status !== 'failed') {
            throw ValidationException::withMessages([
                'status' => ['Only failed sync jobs can be retried.'],
            ]);
        }

        $job->forceFill([
            'status' => 'queued',
            'retried_at' => now(),
            'updated_by_user_id' => $actor->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'integrations.sync-job.retry-requested',
            actor: $actor,
            metadata: [
                'integration_sync_job_id' => $job->id,
                'attempts_count' => $job->attempts_count,
            ],
            entityType: 'integration_sync_job',
            entityId: (string) $job->id,
        );

        return $this->executeJob($job, $actor);
    }

    public function receiveInboundWebhook(string $subscriptionKey, array $payload, string $rawBody, array $headers): IntegrationSyncJob
    {
        $subscription = WebhookSubscription::query()
            ->with(['company', 'connection'])
            ->where('subscription_key', $subscriptionKey)
            ->firstOrFail();

        return $this->runWithCompanyContext($subscription->company()->firstOrFail(), function () use ($subscription, $payload, $rawBody, $headers): IntegrationSyncJob {
            if ($subscription->status !== 'active') {
                throw ValidationException::withMessages([
                    'subscription' => ['This webhook subscription is not active.'],
                ]);
            }

            $connection = $subscription->connection()->firstOrFail();
            $this->ensureConnectionDirectionSupportsSubscription($connection->direction, 'inbound');
            $this->ensureEventSupportsDirection($subscription->event_key, 'inbound');
            $this->validateInboundSignature($subscription, $rawBody, $headers);

            $subscription->forceFill([
                'last_received_at' => now(),
            ])->save();

            $job = $this->queueJobForSubscription(
                subscription: $subscription,
                direction: 'inbound',
                triggerSource: 'webhook',
                requestPayload: $payload,
                entityType: $this->nullableString($payload['entity_type'] ?? null),
                entityId: $this->nullableString($payload['entity_id'] ?? null),
                actor: null,
                requestHeaders: $headers,
            );

            $this->auditLogger->record(
                eventType: 'integrations.webhook.received',
                actor: null,
                metadata: [
                    'webhook_subscription_id' => $subscription->id,
                    'integration_sync_job_id' => $job->id,
                    'event_key' => $subscription->event_key,
                ],
                entityType: 'integration_sync_job',
                entityId: (string) $job->id,
            );

            return $this->executeJob($job, null);
        });
    }

    private function resolveConnection(User $actor, int $connectionId): IntegrationConnection
    {
        return IntegrationConnection::query()
            ->where('company_id', $actor->company_id)
            ->findOrFail($connectionId);
    }

    private function resolveSubscription(User $actor, int $subscriptionId): WebhookSubscription
    {
        return WebhookSubscription::query()
            ->with('connection')
            ->where('company_id', $actor->company_id)
            ->findOrFail($subscriptionId);
    }

    private function resolveSyncJob(User $actor, int $jobId): IntegrationSyncJob
    {
        return IntegrationSyncJob::query()
            ->with(['connection', 'subscription', 'errors'])
            ->where('company_id', $actor->company_id)
            ->findOrFail($jobId);
    }

    private function ensureConnectionDirectionSupportsSubscription(string $connectionDirection, string $subscriptionDirection): void
    {
        if ($connectionDirection === 'bidirectional' || $connectionDirection === $subscriptionDirection) {
            return;
        }

        throw ValidationException::withMessages([
            'direction' => ['The selected connection direction does not support this subscription direction.'],
        ]);
    }

    private function ensureEventSupportsDirection(string $eventKey, string $direction): void
    {
        $event = $this->eventCatalog($eventKey);

        if (in_array($direction, Arr::wrap($event['directions'] ?? []), true)) {
            return;
        }

        throw ValidationException::withMessages([
            'event_key' => ['The selected integration event does not support this direction.'],
        ]);
    }

    private function ensureEventSupportsSystem(string $eventKey, string $systemKey): void
    {
        $event = $this->eventCatalog($eventKey);

        if (in_array($systemKey, Arr::wrap($event['systems'] ?? []), true)) {
            return;
        }

        throw ValidationException::withMessages([
            'event_key' => ['The selected external system is not approved for this integration event.'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventCatalog(string $eventKey): array
    {
        $event = collect(config('integrations.events', []))
            ->first(fn (array $candidate): bool => ($candidate['key'] ?? null) === $eventKey);

        if (! is_array($event)) {
            throw ValidationException::withMessages([
                'event_key' => ['The selected integration event is not registered.'],
            ]);
        }

        return $event;
    }

    /**
     * @param  array<string, mixed>  $dispatchPayload
     */
    private function subscriptionMatchesDispatch(WebhookSubscription $subscription, array $dispatchPayload): bool
    {
        $filters = $subscription->filter_rules ?? [];

        if (! is_array($filters) || $filters === []) {
            return true;
        }

        if (is_array($filters['entity_types'] ?? null)) {
            $entityType = $this->nullableString($dispatchPayload['entity_type'] ?? null);

            if ($entityType === null || ! in_array($entityType, $filters['entity_types'], true)) {
                return false;
            }
        }

        if (is_array($filters['entity_ids'] ?? null)) {
            $entityId = $this->nullableString($dispatchPayload['entity_id'] ?? null);

            if ($entityId === null || ! in_array($entityId, $filters['entity_ids'], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $requestPayload
     * @param  array<string, mixed>|null  $requestHeaders
     */
    private function queueJobForSubscription(
        WebhookSubscription $subscription,
        string $direction,
        string $triggerSource,
        array $requestPayload,
        ?string $entityType,
        ?string $entityId,
        ?User $actor,
        ?array $requestHeaders = null,
    ): IntegrationSyncJob {
        $connection = $subscription->connection()->firstOrFail();

        return IntegrationSyncJob::query()->create([
            'company_id' => $subscription->company_id,
            'integration_connection_id' => $connection->id,
            'webhook_subscription_id' => $subscription->id,
            'job_uuid' => (string) Str::uuid(),
            'version' => $subscription->version,
            'system_key' => $connection->system_key,
            'event_key' => $subscription->event_key,
            'direction' => $direction,
            'status' => 'queued',
            'trigger_source' => $triggerSource,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'request_payload' => $requestPayload,
            'request_headers' => $this->sanitizeHeaders($requestHeaders ?? []),
            'queued_at' => now(),
            'audit_metadata' => [
                'subscription_direction' => $subscription->direction,
                'connection_name' => $connection->name,
                'requested_by_user_id' => $actor?->id,
            ],
            'created_by_user_id' => $actor?->id,
            'updated_by_user_id' => $actor?->id,
        ]);
    }

    private function executeJob(IntegrationSyncJob $job, ?User $actor): IntegrationSyncJob
    {
        $job->forceFill([
            'status' => 'running',
            'started_at' => now(),
            'completed_at' => null,
            'failed_at' => null,
            'last_error' => null,
            'attempts_count' => $job->attempts_count + 1,
            'last_attempt_at' => now(),
            'processed_by_user_id' => $actor?->id,
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $this->auditLogger->record(
            eventType: 'integrations.sync-job.processing',
            actor: $actor,
            metadata: [
                'integration_sync_job_id' => $job->id,
                'direction' => $job->direction,
                'event_key' => $job->event_key,
                'attempts_count' => $job->attempts_count,
            ],
            entityType: 'integration_sync_job',
            entityId: (string) $job->id,
        );

        try {
            $job = $job->direction === 'outbound'
                ? $this->deliverOutboundJob($job, $actor)
                : $this->processInboundJob($job, $actor);

            $this->resolveOutstandingErrors($job, $actor);

            $this->auditLogger->record(
                eventType: 'integrations.sync-job.completed',
                actor: $actor,
                metadata: [
                    'integration_sync_job_id' => $job->id,
                    'direction' => $job->direction,
                    'event_key' => $job->event_key,
                    'attempts_count' => $job->attempts_count,
                ],
                entityType: 'integration_sync_job',
                entityId: (string) $job->id,
            );

            return $job->fresh(['connection', 'subscription', 'errors']);
        } catch (Throwable $exception) {
            $message = $exception instanceof ValidationException
                ? $this->flattenValidationMessage($exception)
                : ($exception->getMessage() !== '' ? $exception->getMessage() : 'The integration sync job failed.');

            $job->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'last_error' => $message,
                'updated_by_user_id' => $actor?->id,
            ])->save();

            $this->recordSyncError($job, $message, $actor);

            $this->auditLogger->record(
                eventType: 'integrations.sync-job.failed',
                actor: $actor,
                metadata: [
                    'integration_sync_job_id' => $job->id,
                    'direction' => $job->direction,
                    'event_key' => $job->event_key,
                    'last_error' => $message,
                ],
                entityType: 'integration_sync_job',
                entityId: (string) $job->id,
            );

            return $job->fresh(['connection', 'subscription', 'errors']);
        }
    }

    private function deliverOutboundJob(IntegrationSyncJob $job, ?User $actor): IntegrationSyncJob
    {
        $subscription = $job->subscription()->with('connection')->firstOrFail();
        $connection = $subscription->connection()->firstOrFail();

        if (! $subscription->endpoint_url) {
            throw ValidationException::withMessages([
                'endpoint_url' => ['Outbound subscriptions require an endpoint URL before dispatch can run.'],
            ]);
        }

        $requestBody = [
            'version' => $job->version,
            'event_key' => $job->event_key,
            'job_uuid' => $job->job_uuid,
            'system_key' => $job->system_key,
            'entity_type' => $job->entity_type,
            'entity_id' => $job->entity_id,
            'trigger_source' => $job->trigger_source,
            'occurred_at' => $job->queued_at?->toIso8601String(),
            'payload' => $job->request_payload ?? [],
        ];

        $encodedPayload = json_encode($requestBody, JSON_THROW_ON_ERROR);
        $signatureHeader = (string) config('integrations.signature_header', 'X-PhoenixHRMS-Signature');
        $requestIdHeader = (string) config('integrations.request_id_header', 'X-PhoenixHRMS-Request-Id');

        $requestHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-PhoenixHRMS-Event' => $job->event_key,
            'X-PhoenixHRMS-Version' => $job->version,
            $requestIdHeader => $job->job_uuid,
            $signatureHeader => $this->signPayload($subscription->secret, $encodedPayload),
        ] + $this->normalizeHeaders($subscription->custom_headers ?? []);

        $response = Http::timeout(10)
            ->withHeaders($requestHeaders)
            ->post($subscription->endpoint_url, $requestBody);

        $responsePayload = $this->normalizeResponsePayload($response->json(), $response->body(), $response->status());
        $responseHeaders = $this->sanitizeHeaders($response->headers());

        $job->forceFill([
            'request_headers' => $this->sanitizeHeaders($requestHeaders),
            'response_payload' => $responsePayload,
            'response_headers' => $responseHeaders,
        ])->save();

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'endpoint_url' => ["The external endpoint returned HTTP {$response->status()}."],
            ]);
        }

        $subscription->forceFill([
            'last_delivery_at' => now(),
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $connection->forceFill([
            'last_synced_at' => now(),
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $job->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'failed_at' => null,
            'last_error' => null,
            'updated_by_user_id' => $actor?->id,
        ])->save();

        return $job;
    }

    private function processInboundJob(IntegrationSyncJob $job, ?User $actor): IntegrationSyncJob
    {
        $subscription = $job->subscription()->with('connection')->firstOrFail();
        $connection = $subscription->connection()->firstOrFail();
        $requestPayload = $job->request_payload ?? [];

        if ((bool) ($requestPayload['simulate_failure'] ?? false)) {
            throw ValidationException::withMessages([
                'payload' => ['The inbound payload requested a simulated failure for retry validation.'],
            ]);
        }

        $records = is_array($requestPayload['records'] ?? null) ? $requestPayload['records'] : [];

        $job->forceFill([
            'response_payload' => [
                'accepted' => true,
                'handled_as' => $job->event_key,
                'received_records' => count($records),
                'queued_for_review' => true,
            ],
            'response_headers' => [],
            'status' => 'completed',
            'completed_at' => now(),
            'failed_at' => null,
            'last_error' => null,
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $subscription->forceFill([
            'last_received_at' => now(),
            'updated_by_user_id' => $actor?->id,
        ])->save();

        $connection->forceFill([
            'last_synced_at' => now(),
            'updated_by_user_id' => $actor?->id,
        ])->save();

        return $job;
    }

    private function recordSyncError(IntegrationSyncJob $job, string $message, ?User $actor): IntegrationSyncError
    {
        return IntegrationSyncError::query()->create([
            'company_id' => $job->company_id,
            'integration_sync_job_id' => $job->id,
            'attempt_number' => $job->attempts_count,
            'error_code' => $job->direction === 'outbound' ? 'delivery_failed' : 'ingestion_failed',
            'error_message' => $message,
            'request_payload' => $job->request_payload,
            'response_payload' => $job->response_payload,
            'request_headers' => $job->request_headers,
            'response_headers' => $job->response_headers,
            'occurred_at' => now(),
            'resolved_by_user_id' => null,
        ]);
    }

    private function resolveOutstandingErrors(IntegrationSyncJob $job, ?User $actor): void
    {
        IntegrationSyncError::query()
            ->where('integration_sync_job_id', $job->id)
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => now(),
                'resolved_by_user_id' => $actor?->id,
                'updated_at' => now(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $headers
     */
    private function validateInboundSignature(WebhookSubscription $subscription, string $rawBody, array $headers): void
    {
        $signatureHeader = strtolower((string) config('integrations.signature_header', 'X-PhoenixHRMS-Signature'));
        $signature = $this->firstHeaderValue($headers, $signatureHeader);
        $expected = $this->signPayload($subscription->secret, $rawBody);

        if (! is_string($signature) || ! hash_equals($expected, $signature)) {
            $this->auditLogger->record(
                eventType: 'integrations.webhook.rejected',
                actor: null,
                metadata: [
                    'webhook_subscription_id' => $subscription->id,
                    'event_key' => $subscription->event_key,
                    'reason' => 'signature_mismatch',
                ],
                entityType: 'webhook_subscription',
                entityId: (string) $subscription->id,
            );

            throw ValidationException::withMessages([
                'signature' => ['The inbound webhook signature is invalid.'],
            ]);
        }
    }

    private function signPayload(string $secret, string $payload): string
    {
        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }

    /**
     * @param  array<string, mixed>  $headers
     */
    private function firstHeaderValue(array $headers, string $targetHeader): ?string
    {
        foreach ($headers as $key => $value) {
            if (strtolower((string) $key) !== strtolower($targetHeader)) {
                continue;
            }

            if (is_array($value)) {
                $first = $value[0] ?? null;

                return is_scalar($first) ? (string) $first : null;
            }

            return is_scalar($value) ? (string) $value : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $headers
     * @return array<string, mixed>
     */
    private function sanitizeHeaders(?array $headers): array
    {
        if (! is_array($headers)) {
            return [];
        }

        $sanitized = [];

        foreach ($headers as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            $sanitized[$key] = str_contains($normalizedKey, 'authorization')
                || str_contains($normalizedKey, 'token')
                || str_contains($normalizedKey, 'secret')
                || str_contains($normalizedKey, 'signature')
                    ? '[redacted]'
                    : $value;
        }

        return $sanitized;
    }

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        return collect($headers)
            ->mapWithKeys(fn (mixed $value, mixed $key): array => [(string) $key => is_scalar($value) ? (string) $value : json_encode($value)])
            ->all();
    }

    /**
     * @param  mixed  $json
     * @return array<string, mixed>
     */
    private function normalizeResponsePayload(mixed $json, string $body, int $status): array
    {
        if (is_array($json)) {
            return [
                'status_code' => $status,
                'body' => $json,
            ];
        }

        return [
            'status_code' => $status,
            'body' => [
                'raw' => Str::limit($body, 2000, ''),
            ],
        ];
    }

    private function flattenValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())
            ->flatten()
            ->filter(fn (mixed $message): bool => is_string($message) && $message !== '')
            ->implode(' ');
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    private function runWithCompanyContext(Company $company, callable $callback): mixed
    {
        $previousContext = app(TenantContext::class);
        $previousTimezone = (string) config('app.timezone');
        $previousLocale = app()->getLocale();

        app()->instance(TenantContext::class, TenantContext::fromCompany($company));
        config(['app.timezone' => $company->timezone]);
        config(['app.locale' => $company->language]);
        date_default_timezone_set($company->timezone);
        app()->setLocale($company->language);

        try {
            return $callback();
        } finally {
            app()->instance(TenantContext::class, $previousContext);
            config(['app.timezone' => $previousTimezone]);
            config(['app.locale' => $previousLocale]);
            date_default_timezone_set($previousTimezone);
            app()->setLocale($previousLocale);
        }
    }
}
