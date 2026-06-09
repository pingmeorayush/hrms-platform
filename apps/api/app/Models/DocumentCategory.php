<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
