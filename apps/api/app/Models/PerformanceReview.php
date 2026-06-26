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
 * @property int $performance_review_cycle_id
 * @property int $employee_id
 * @property int|null $manager_employee_id
 * @property array<int, int>|null $reviewer_user_ids
 * @property array<int, array<string, mixed>>|null $goal_snapshot
 * @property array<int, array<string, mixed>>|null $competency_snapshot
 * @property array<string, mixed>|null $visibility_rules
 * @property string $status
 * @property array<string, mixed>|null $calibration_payload
 * @property array<string, mixed>|null $final_payload
 * @property Carbon|null $launched_at
 * @property Carbon|null $self_submitted_at
 * @property Carbon|null $manager_submitted_at
 * @property Carbon|null $calibration_completed_at
 * @property Carbon|null $finalized_at
 * @property Carbon|null $published_at
 * @property Carbon|null $reopened_at
 * @property int $reopen_count
 * @property string|null $reopened_reason
 * @property-read Company|null $company
 * @property-read PerformanceReviewCycle|null $reviewCycle
 * @property-read Employee|null $employee
 * @property-read Employee|null $managerEmployee
 * @property-read EloquentCollection<int, PerformanceReviewSubmission> $submissions
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'performance_review_cycle_id',
    'employee_id',
    'manager_employee_id',
    'reviewer_user_ids',
    'goal_snapshot',
    'competency_snapshot',
    'visibility_rules',
    'status',
    'launched_at',
    'self_submitted_at',
    'manager_submitted_at',
    'calibration_completed_at',
    'finalized_at',
    'published_at',
    'reopened_at',
    'reopen_count',
    'reopened_reason',
    'calibration_payload',
    'final_payload',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PerformanceReview extends Model
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
     * @return BelongsTo<PerformanceReviewCycle, $this>
     */
    public function reviewCycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceReviewCycle::class, 'performance_review_cycle_id');
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function managerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_employee_id');
    }

    /**
     * @return HasMany<PerformanceReviewSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(PerformanceReviewSubmission::class)
            ->orderBy('submitted_at')
            ->orderBy('id');
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
            'reviewer_user_ids' => 'array',
            'goal_snapshot' => 'array',
            'competency_snapshot' => 'array',
            'visibility_rules' => 'array',
            'calibration_payload' => 'array',
            'final_payload' => 'array',
            'launched_at' => 'datetime',
            'self_submitted_at' => 'datetime',
            'manager_submitted_at' => 'datetime',
            'calibration_completed_at' => 'datetime',
            'finalized_at' => 'datetime',
            'published_at' => 'datetime',
            'reopened_at' => 'datetime',
            'reopen_count' => 'integer',
        ];
    }
}
