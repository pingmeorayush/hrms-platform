<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $integration_connection_id
 * @property int|null $webhook_subscription_id
 * @property string $job_uuid
 * @property string $version
 * @property string $system_key
 * @property string $event_key
 * @property string $direction
 * @property string $status
 * @property string $trigger_source
 * @property string|null $entity_type
 * @property string|null $entity_id
 * @property array<string, mixed>|null $request_payload
 * @property array<string, mixed>|null $response_payload
 * @property array<string, mixed>|null $request_headers
 * @property array<string, mixed>|null $response_headers
 * @property int $attempts_count
 * @property \Illuminate\Support\Carbon|null $last_attempt_at
 * @property \Illuminate\Support\Carbon|null $queued_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property \Illuminate\Support\Carbon|null $retried_at
 * @property string|null $last_error
 * @property array<string, mixed>|null $audit_metadata
 * @property-read Company|null $company
 * @property-read IntegrationConnection|null $connection
 * @property-read WebhookSubscription|null $subscription
 * @property-read User|null $processedBy
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, IntegrationSyncError> $errors
 */
#[Fillable([
    'company_id',
    'integration_connection_id',
    'webhook_subscription_id',
    'job_uuid',
    'version',
    'system_key',
    'event_key',
    'direction',
    'status',
    'trigger_source',
    'entity_type',
    'entity_id',
    'request_payload',
    'response_payload',
    'request_headers',
    'response_headers',
    'attempts_count',
    'last_attempt_at',
    'queued_at',
    'started_at',
    'completed_at',
    'failed_at',
    'retried_at',
    'last_error',
    'audit_metadata',
    'processed_by_user_id',
    'created_by_user_id',
    'updated_by_user_id',
])]
class IntegrationSyncJob extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<IntegrationConnection, $this>
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(IntegrationConnection::class, 'integration_connection_id');
    }

    /**
     * @return BelongsTo<WebhookSubscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'webhook_subscription_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * @return HasMany<IntegrationSyncError, $this>
     */
    public function errors(): HasMany
    {
        return $this->hasMany(IntegrationSyncError::class, 'integration_sync_job_id');
    }

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'request_headers' => 'array',
            'response_headers' => 'array',
            'audit_metadata' => 'array',
            'attempts_count' => 'integer',
            'last_attempt_at' => 'datetime',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'retried_at' => 'datetime',
        ];
    }
}
