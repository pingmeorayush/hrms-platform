<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmployeeLifecycleTaskTemplate::class, 'template_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function latestActionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'latest_action_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

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
