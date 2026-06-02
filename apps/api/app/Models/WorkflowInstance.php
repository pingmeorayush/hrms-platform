<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id');
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkflowTask::class)->orderBy('sequence');
    }
}
