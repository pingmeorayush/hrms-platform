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
 * @property string $code
 * @property string $name
 * @property string $cycle_type
 * @property Carbon|null $starts_on
 * @property Carbon|null $ends_on
 * @property Carbon|null $self_review_due_on
 * @property Carbon|null $manager_review_due_on
 * @property Carbon|null $calibration_starts_on
 * @property Carbon|null $calibration_ends_on
 * @property Carbon|null $publish_on
 * @property array<string, mixed>|null $participant_rules
 * @property array<string, mixed>|null $review_template
 * @property array<string, mixed>|null $competency_visibility
 * @property string $status
 * @property-read Company|null $company
 * @property-read EloquentCollection<int, PerformanceGoal> $goals
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'code',
    'name',
    'cycle_type',
    'starts_on',
    'ends_on',
    'self_review_due_on',
    'manager_review_due_on',
    'calibration_starts_on',
    'calibration_ends_on',
    'publish_on',
    'participant_rules',
    'review_template',
    'competency_visibility',
    'status',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PerformanceReviewCycle extends Model
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
     * @return HasMany<PerformanceGoal, $this>
     */
    public function goals(): HasMany
    {
        return $this->hasMany(PerformanceGoal::class);
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

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'self_review_due_on' => 'date',
            'manager_review_due_on' => 'date',
            'calibration_starts_on' => 'date',
            'calibration_ends_on' => 'date',
            'publish_on' => 'date',
            'participant_rules' => 'array',
            'review_template' => 'array',
            'competency_visibility' => 'array',
        ];
    }
}
