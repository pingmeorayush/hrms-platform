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
 * @property string $name
 * @property string $lifecycle_type
 * @property string $title
 * @property string $category
 * @property string|null $task_type
 * @property string $assignee_type
 * @property bool $requires_approval
 * @property string|null $approval_workflow_key
 * @property int|null $due_offset_days
 * @property int $sort_order
 * @property string|null $notes
 * @property bool $is_active
 * @property-read EloquentCollection<int, EmployeeOnboardingTask> $tasks
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'name',
    'lifecycle_type',
    'title',
    'category',
    'task_type',
    'assignee_type',
    'requires_approval',
    'approval_workflow_key',
    'due_offset_days',
    'sort_order',
    'notes',
    'is_active',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeLifecycleTaskTemplate extends Model
{
    use BelongsToCompany;

    /**
     * @return HasMany<EmployeeOnboardingTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class, 'template_id');
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
            'requires_approval' => 'boolean',
            'due_offset_days' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
