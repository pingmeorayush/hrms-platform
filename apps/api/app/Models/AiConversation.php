<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'user_id',
    'title',
    'persona',
    'status',
    'metadata',
    'last_interacted_at',
])]
class AiConversation extends Model
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<AiInteraction, $this>
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(AiInteraction::class)
            ->orderByDesc('responded_at')
            ->orderByDesc('id');
    }

    /**
     * @return HasMany<AiRecommendation, $this>
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'last_interacted_at' => 'datetime',
        ];
    }
}
