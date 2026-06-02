<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'workflow_instance_id',
    'workflow_stage_id',
    'stage_key',
    'stage_name',
    'sequence',
    'assigned_to_user_id',
    'assigned_to_role',
    'status',
    'available_actions',
    'decision',
    'decision_comment',
    'acted_by_user_id',
    'delegated_to_user_id',
    'acted_at',
    'due_at',
    'escalated_at',
    'metadata',
])]
class WorkflowTask extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'available_actions' => 'array',
            'metadata' => 'array',
            'acted_at' => 'datetime',
            'due_at' => 'datetime',
            'escalated_at' => 'datetime',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }
}
