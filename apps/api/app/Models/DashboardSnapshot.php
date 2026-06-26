<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $dashboard_key
 * @property string $scope_signature
 * @property string $source_signature
 * @property int $freshness_expectation_minutes
 * @property Carbon $generated_at
 * @property Carbon $expires_at
 * @property array<string, mixed> $payload
 * @property-read Company|null $company
 */
#[Fillable([
    'company_id',
    'dashboard_key',
    'scope_signature',
    'source_signature',
    'freshness_expectation_minutes',
    'generated_at',
    'expires_at',
    'payload',
])]
class DashboardSnapshot extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected function casts(): array
    {
        return [
            'freshness_expectation_minutes' => 'integer',
            'generated_at' => 'datetime',
            'expires_at' => 'datetime',
            'payload' => 'array',
        ];
    }
}
