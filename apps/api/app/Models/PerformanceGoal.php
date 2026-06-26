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
 * @property int|null $performance_review_cycle_id
 * @property int $owner_employee_id
 * @property int|null $department_id
 * @property string $goal_code
 * @property string $goal_type
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $due_on
 * @property float $weight_percent
 * @property array<string, mixed>|null $success_metric
 * @property string $status
 * @property-read Company|null $company
 * @property-read PerformanceReviewCycle|null $reviewCycle
 * @property-read Employee|null $ownerEmployee
 * @property-read Department|null $department
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'performance_review_cycle_id',
    'owner_employee_id',
    'department_id',
    'goal_code',
    'goal_type',
    'title',
    'description',
    'due_on',
    'weight_percent',
    'success_metric',
    'status',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PerformanceGoal extends Model
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
    public function ownerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'owner_employee_id');
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
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
            'due_on' => 'date',
            'weight_percent' => 'float',
            'success_metric' => 'array',
        ];
    }
}
