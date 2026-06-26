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
 * @property int $learning_assignment_id
 * @property int $learning_item_id
 * @property int $employee_id
 * @property int $assigned_by_user_id
 * @property Carbon|null $assigned_on
 * @property Carbon|null $due_on
 * @property string $status
 * @property int $completion_progress_percent
 * @property Carbon|null $completed_at
 * @property int|null $completed_by_user_id
 * @property string|null $completion_notes
 * @property array<string, mixed>|null $completion_evidence
 * @property Carbon|null $renewal_due_on
 * @property-read Company|null $company
 * @property-read LearningAssignment|null $assignment
 * @property-read LearningItem|null $item
 * @property-read Employee|null $employee
 * @property-read User|null $assignedBy
 * @property-read User|null $completedBy
 */
#[Fillable([
    'company_id',
    'learning_assignment_id',
    'learning_item_id',
    'employee_id',
    'assigned_by_user_id',
    'assigned_on',
    'due_on',
    'status',
    'completion_progress_percent',
    'completed_at',
    'completed_by_user_id',
    'completion_notes',
    'completion_evidence',
    'renewal_due_on',
])]
class LearningAssignmentTarget extends Model
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
     * @return BelongsTo<LearningAssignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LearningAssignment::class, 'learning_assignment_id');
    }

    /**
     * @return BelongsTo<LearningItem, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(LearningItem::class, 'learning_item_id');
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
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'assigned_on' => 'date',
            'due_on' => 'date',
            'completion_progress_percent' => 'integer',
            'completed_at' => 'datetime',
            'completion_evidence' => 'array',
            'renewal_due_on' => 'date',
        ];
    }
}
