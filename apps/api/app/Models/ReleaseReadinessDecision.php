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
 * @property string $release_window_label
 * @property string $target_environment
 * @property string $decision_status
 * @property string $summary
 * @property array<int, array<string, mixed>>|null $blockers
 * @property array<int, string>|null $artifact_refs
 * @property array<int, array<string, mixed>>|null $checklist_snapshot
 * @property string|null $decision_notes
 * @property Carbon|null $decided_at
 * @property int|null $decided_by_user_id
 * @property-read User|null $decidedBy
 */
#[Fillable([
    'company_id',
    'release_window_label',
    'target_environment',
    'decision_status',
    'summary',
    'blockers',
    'artifact_refs',
    'checklist_snapshot',
    'decision_notes',
    'decided_at',
    'decided_by_user_id',
])]
class ReleaseReadinessDecision extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<User, $this>
     */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'blockers' => 'array',
            'artifact_refs' => 'array',
            'checklist_snapshot' => 'array',
            'decided_at' => 'datetime',
        ];
    }
}
