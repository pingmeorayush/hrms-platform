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
    'employee_id',
    'scenario',
    'title',
    'summary',
    'rationale',
    'confidence_score',
    'suggested_actions',
    'supporting_citations',
    'status',
    'human_review_required',
    'decision',
    'decision_notes',
    'decided_at',
    'decided_by_user_id',
    'metadata',
])]
class AiRecommendation extends Model
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

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

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
            'rationale' => 'array',
            'confidence_score' => 'float',
            'suggested_actions' => 'array',
            'supporting_citations' => 'array',
            'human_review_required' => 'boolean',
            'decided_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
