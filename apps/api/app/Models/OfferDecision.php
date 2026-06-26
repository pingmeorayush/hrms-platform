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
 * @property int $offer_id
 * @property string|null $from_status
 * @property string $to_status
 * @property string $decision_type
 * @property string|null $comment
 * @property int|null $acted_by_user_id
 * @property Carbon|null $acted_at
 * @property-read Offer|null $offer
 * @property-read User|null $actor
 */
#[Fillable([
    'company_id',
    'offer_id',
    'from_status',
    'to_status',
    'decision_type',
    'comment',
    'acted_by_user_id',
    'acted_at',
])]
class OfferDecision extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Offer, $this>
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }
}
