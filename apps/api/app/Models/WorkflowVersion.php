<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $workflow_definition_id
 * @property int $version
 * @property string $status
 * @property array<string, mixed>|null $definition
 * @property Carbon|null $published_at
 * @property-read WorkflowDefinition|null $definitionModel
 * @property-read EloquentCollection<int, WorkflowStage> $stages
 */
#[Fillable([
    'workflow_definition_id',
    'version',
    'status',
    'definition',
    'created_by',
    'published_at',
])]
class WorkflowVersion extends Model
{
    protected function casts(): array
    {
        return [
            'definition' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WorkflowDefinition, $this>
     */
    public function definitionModel(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    /**
     * @return HasMany<WorkflowStage, $this>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('sequence');
    }
}
