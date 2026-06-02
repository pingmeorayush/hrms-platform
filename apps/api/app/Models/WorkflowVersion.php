<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function definitionModel(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('sequence');
    }
}
