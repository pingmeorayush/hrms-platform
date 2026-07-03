<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'ai_conversation_id',
    'user_id',
    'interaction_type',
    'use_case',
    'question',
    'answer',
    'status',
    'confidence_score',
    'citations',
    'guardrails',
    'metadata',
    'feedback_rating',
    'feedback_sentiment',
    'feedback_notes',
    'responded_at',
])]
class AiInteraction extends Model
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
     * @return BelongsTo<AiConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'confidence_score' => 'float',
            'citations' => 'array',
            'guardrails' => 'array',
            'metadata' => 'array',
            'feedback_rating' => 'integer',
            'responded_at' => 'datetime',
        ];
    }
}
