<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workflow_version_id',
    'key',
    'name',
    'sequence',
    'approver_type',
    'approver_value',
    'available_actions',
    'sla_hours',
    'metadata',
])]
class WorkflowStage extends Model
{
    protected function casts(): array
    {
        return [
            'available_actions' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<WorkflowVersion, $this>
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id');
    }
}
