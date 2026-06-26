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
 * @property int $workflow_definition_id
 * @property int $workflow_version_id
 * @property string $reference_type
 * @property string $reference_id
 * @property string $status
 * @property int|null $current_stage_sequence
 * @property int|null $started_by_user_id
 * @property array<string, mixed>|null $payload
 * @property Carbon|null $completed_at
 * @property Carbon|null $rejected_at
 * @property-read WorkflowDefinition|null $definition
 * @property-read WorkflowVersion|null $version
 * @property-read User|null $starter
 * @property-read EloquentCollection<int, WorkflowTask> $tasks
 */
#[Fillable([
    'company_id',
    'workflow_definition_id',
    'workflow_version_id',
    'reference_type',
    'reference_id',
    'status',
    'current_stage_sequence',
    'started_by_user_id',
    'payload',
    'completed_at',
    'rejected_at',
])]
class WorkflowInstance extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'completed_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WorkflowDefinition, $this>
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    /**
     * @return BelongsTo<WorkflowVersion, $this>
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    /**
     * @return HasMany<WorkflowTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(WorkflowTask::class)->orderBy('sequence');
    }
}
