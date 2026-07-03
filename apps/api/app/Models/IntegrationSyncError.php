<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $integration_sync_job_id
 * @property int $attempt_number
 * @property string|null $error_code
 * @property string $error_message
 * @property array<string, mixed>|null $request_payload
 * @property array<string, mixed>|null $response_payload
 * @property array<string, mixed>|null $request_headers
 * @property array<string, mixed>|null $response_headers
 * @property \Illuminate\Support\Carbon $occurred_at
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property-read Company|null $company
 * @property-read IntegrationSyncJob|null $job
 * @property-read User|null $resolvedBy
 */
#[Fillable([
    'company_id',
    'integration_sync_job_id',
    'attempt_number',
    'error_code',
    'error_message',
    'request_payload',
    'response_payload',
    'request_headers',
    'response_headers',
    'occurred_at',
    'resolved_at',
    'resolved_by_user_id',
])]
class IntegrationSyncError extends Model
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
     * @return BelongsTo<IntegrationSyncJob, $this>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(IntegrationSyncJob::class, 'integration_sync_job_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'request_headers' => 'array',
            'response_headers' => 'array',
            'occurred_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }
}
