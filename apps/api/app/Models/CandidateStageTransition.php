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
 * @property int $candidate_id
 * @property string|null $from_stage
 * @property string $to_stage
 * @property string $resulting_status
 * @property string|null $comment
 * @property int|null $transitioned_by_user_id
 * @property Carbon|null $transitioned_at
 * @property-read Candidate|null $candidate
 * @property-read User|null $actor
 */
#[Fillable([
    'company_id',
    'candidate_id',
    'from_stage',
    'to_stage',
    'resulting_status',
    'comment',
    'transitioned_by_user_id',
    'transitioned_at',
])]
class CandidateStageTransition extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transitioned_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'transitioned_at' => 'datetime',
        ];
    }
}
