<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company_id',
    'asset_category_id',
    'asset_tag',
    'name',
    'asset_type',
    'serial_number',
    'manufacturer',
    'model_name',
    'purchase_date',
    'status',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class Asset extends Model
{
    use BelongsToCompany;

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)
            ->orderByDesc('assigned_at')
            ->orderByDesc('id');
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)
            ->ofMany(['id' => 'max'], function ($query): void {
                $query->whereIn('status', ['assigned', 'issued']);
            });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'asset_category_id' => 'integer',
            'purchase_date' => 'date',
        ];
    }
}
