<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $key
 * @property string $name
 * @property string $module
 * @property string|null $description
 * @property bool $is_template
 * @property string $status
 * @property int|null $active_version_id
 * @property-read Company|null $company
 * @property-read WorkflowVersion|null $activeVersion
 * @property-read EloquentCollection<int, WorkflowVersion> $versions
 */
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

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<WorkflowVersion, $this>
     */
    public function activeVersion(): BelongsTo
    {
        return $this->belongsTo(WorkflowVersion::class, 'active_version_id');
    }

    /**
     * @return HasMany<WorkflowVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(WorkflowVersion::class)->orderByDesc('version');
    }
}
