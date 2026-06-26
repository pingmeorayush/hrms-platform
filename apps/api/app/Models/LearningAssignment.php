<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $learning_item_id
 * @property string $assignment_code
 * @property int $assigned_by_user_id
 * @property string $audience_type
 * @property array<string, mixed>|null $audience_rules
 * @property Carbon|null $assigned_on
 * @property Carbon|null $due_on
 * @property array<string, mixed>|null $completion_rules
 * @property string|null $notes
 * @property string $status
 * @property int $target_count
 * @property int $completion_count
 * @property-read Company|null $company
 * @property-read LearningItem|null $item
 * @property-read User|null $assignedBy
 * @property-read EloquentCollection<int, LearningAssignmentTarget> $targets
 */
#[Fillable([
    'company_id',
    'learning_item_id',
    'assignment_code',
    'assigned_by_user_id',
    'audience_type',
    'audience_rules',
    'assigned_on',
    'due_on',
    'completion_rules',
    'notes',
    'status',
    'target_count',
    'completion_count',
])]
class LearningAssignment extends Model
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
     * @return BelongsTo<LearningItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(LearningItem::class, 'learning_item_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /**
     * @return HasMany<LearningAssignmentTarget, $this>
     */
    public function targets(): HasMany
    {
        return $this->hasMany(LearningAssignmentTarget::class)
            ->orderBy('due_on')
            ->orderByDesc('id');
    }

    protected function casts(): array
    {
        return [
            'audience_rules' => 'array',
            'assigned_on' => 'date',
            'due_on' => 'date',
            'completion_rules' => 'array',
            'target_count' => 'integer',
            'completion_count' => 'integer',
        ];
    }
}
