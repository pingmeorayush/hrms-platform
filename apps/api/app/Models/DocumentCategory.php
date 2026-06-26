<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $code
 * @property string $name
 * @property string $repository_scope
 * @property string|null $default_visibility_scope
 * @property int|null $retention_days
 * @property array<int, string>|null $allowed_role_names
 * @property string $status
 * @property string|null $notes
 * @property-read EloquentCollection<int, Document> $documents
 */
#[Fillable([
    'company_id',
    'code',
    'name',
    'repository_scope',
    'default_visibility_scope',
    'retention_days',
    'allowed_role_names',
    'status',
    'notes',
])]
class DocumentCategory extends Model
{
    use BelongsToCompany;

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    protected function casts(): array
    {
        return [
            'retention_days' => 'integer',
            'allowed_role_names' => 'array',
        ];
    }
}
