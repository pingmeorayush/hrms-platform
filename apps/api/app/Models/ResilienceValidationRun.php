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
 * @property string $scenario_key
 * @property string $scenario_name
 * @property string $scenario_type
 * @property string $environment
 * @property string $status
 * @property int|null $recovery_point_actual_minutes
 * @property int|null $recovery_time_actual_minutes
 * @property array<int, string>|null $evidence_refs
 * @property string|null $notes
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property int|null $executed_by_user_id
 * @property-read User|null $executedBy
 */
#[Fillable([
    'company_id',
    'scenario_key',
    'scenario_name',
    'scenario_type',
    'environment',
    'status',
    'recovery_point_actual_minutes',
    'recovery_time_actual_minutes',
    'evidence_refs',
    'notes',
    'started_at',
    'completed_at',
    'executed_by_user_id',
])]
class ResilienceValidationRun extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<User, $this>
     */
    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'evidence_refs' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
