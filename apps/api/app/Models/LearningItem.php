<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $code
 * @property string $title
 * @property string|null $description
 * @property string|null $category
 * @property string|null $delivery_mode
 * @property int|null $duration_minutes
 * @property bool $requires_completion_evidence
 * @property int|null $renewal_frequency_months
 * @property int|null $default_due_days
 * @property array<string, mixed>|null $metadata
 * @property string $status
 * @property-read Company|null $company
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 * @property-read EloquentCollection<int, LearningAssignment> $assignments
 * @property-read EloquentCollection<int, LearningAssignmentTarget> $targets
 */
#[Fillable([
    'company_id',
    'code',
    'title',
    'description',
    'category',
    'delivery_mode',
    'duration_minutes',
    'requires_completion_evidence',
    'renewal_frequency_months',
    'default_due_days',
    'metadata',
    'status',
    'created_by_user_id',
    'updated_by_user_id',
])]
class LearningItem extends Model
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
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * @return HasMany<LearningAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(LearningAssignment::class);
    }

    /**
     * @return HasMany<LearningAssignmentTarget, $this>
     */
    public function targets(): HasMany
    {
        return $this->hasMany(LearningAssignmentTarget::class);
    }

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'requires_completion_evidence' => 'boolean',
            'renewal_frequency_months' => 'integer',
            'default_due_days' => 'integer',
            'metadata' => 'array',
        ];
    }
}
