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
 * @property string $system_key
 * @property string $version
 * @property string $name
 * @property string $direction
 * @property string $status
 * @property string $auth_mode
 * @property string|null $endpoint_url
 * @property string|null $description
 * @property array<int, string>|null $scopes
 * @property array<string, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $last_synced_at
 * @property-read Company|null $company
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WebhookSubscription> $webhookSubscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, IntegrationSyncJob> $syncJobs
 */
#[Fillable([
    'company_id',
    'system_key',
    'version',
    'name',
    'direction',
    'status',
    'auth_mode',
    'endpoint_url',
    'description',
    'scopes',
    'settings',
    'last_synced_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class IntegrationConnection extends Model
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
     * @return HasMany<WebhookSubscription, $this>
     */
    public function webhookSubscriptions(): HasMany
    {
        return $this->hasMany(WebhookSubscription::class, 'integration_connection_id');
    }

    /**
     * @return HasMany<IntegrationSyncJob, $this>
     */
    public function syncJobs(): HasMany
    {
        return $this->hasMany(IntegrationSyncJob::class, 'integration_connection_id');
    }

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'settings' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }
}
