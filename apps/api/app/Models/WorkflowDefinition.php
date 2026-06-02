<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'key',
    'name',
    'module',
    'description',
    'is_template',
    'status',
    'active_version_id',
    'created_by',
    'updated_by',
])]
class WorkflowDefinition extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'is_template' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function activeVersion(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'active_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(WorkflowVersion::class)->orderByDesc('version');
    }
}
