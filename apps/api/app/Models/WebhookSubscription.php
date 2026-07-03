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
 * @property int $integration_connection_id
 * @property string $subscription_key
 * @property string $version
 * @property string $event_key
 * @property string $direction
 * @property string $status
 * @property string|null $endpoint_url
 * @property string $secret
 * @property array<string, string>|null $custom_headers
 * @property array<string, mixed>|null $filter_rules
 * @property \Illuminate\Support\Carbon|null $last_delivery_at
 * @property \Illuminate\Support\Carbon|null $last_received_at
 * @property-read Company|null $company
 * @property-read IntegrationConnection|null $connection
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, IntegrationSyncJob> $syncJobs
 */
#[Fillable([
    'company_id',
    'integration_connection_id',
    'subscription_key',
    'version',
    'event_key',
    'direction',
    'status',
    'endpoint_url',
    'secret',
    'custom_headers',
    'filter_rules',
    'last_delivery_at',
    'last_received_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class WebhookSubscription extends Model
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
     * @return HasMany<IntegrationSyncJob, $this>
     */
    public function syncJobs(): HasMany
    {
        return $this->hasMany(IntegrationSyncJob::class, 'webhook_subscription_id');
    }

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'custom_headers' => 'array',
            'filter_rules' => 'array',
            'last_delivery_at' => 'datetime',
            'last_received_at' => 'datetime',
        ];
    }
}
