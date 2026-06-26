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
 * @property int $workflow_instance_id
 * @property int $workflow_stage_id
 * @property int|null $assigned_to_user_id
 * @property int|null $acted_by_user_id
 * @property string $status
 * @property string|null $decision_comment
 * @property array<int, string>|null $available_actions
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $acted_at
 * @property-read WorkflowInstance|null $instance
 * @property-read WorkflowStage|null $stage
 * @property-read User|null $assignee
 * @property-read User|null $actor
 */
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

    /**
     * @return BelongsTo<WorkflowInstance, $this>
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    /**
     * @return BelongsTo<WorkflowStage, $this>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }
}
