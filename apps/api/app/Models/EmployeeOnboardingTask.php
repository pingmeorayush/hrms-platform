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
 * @property int $employee_id
 * @property string $lifecycle_type
 * @property int|null $template_id
 * @property string $title
 * @property string $category
 * @property string|null $task_type
 * @property string $assignee_type
 * @property int|null $assigned_to_user_id
 * @property bool $requires_approval
 * @property string|null $approval_workflow_key
 * @property int|null $workflow_instance_id
 * @property string $status
 * @property int $sort_order
 * @property Carbon|null $due_date
 * @property Carbon|null $completed_at
 * @property int|null $completed_by_user_id
 * @property int|null $latest_action_by_user_id
 * @property Carbon|null $approved_at
 * @property string|null $notes
 * @property-read Employee|null $employee
 * @property-read EmployeeLifecycleTaskTemplate|null $template
 * @property-read User|null $assignedTo
 * @property-read WorkflowInstance|null $workflowInstance
 * @property-read User|null $completedBy
 * @property-read User|null $latestActionBy
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'employee_id',
    'lifecycle_type',
    'template_id',
    'title',
    'category',
    'task_type',
    'assignee_type',
    'assigned_to_user_id',
    'requires_approval',
    'approval_workflow_key',
    'workflow_instance_id',
    'status',
    'sort_order',
    'due_date',
    'completed_at',
    'completed_by_user_id',
    'latest_action_by_user_id',
    'approved_at',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeOnboardingTask extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<EmployeeLifecycleTaskTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmployeeLifecycleTaskTemplate::class, 'template_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * @return BelongsTo<WorkflowInstance, $this>
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function latestActionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'latest_action_by_user_id');
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
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
            'requires_approval' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
