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
    'status',
    'notes',
])]
class AssetCategory extends Model
{
    use BelongsToCompany;

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
