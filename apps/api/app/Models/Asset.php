<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $asset_category_id
 * @property string $asset_tag
 * @property string $name
 * @property string $asset_type
 * @property string|null $serial_number
 * @property string|null $manufacturer
 * @property string|null $model_name
 * @property Carbon|null $purchase_date
 * @property string $status
 * @property string|null $notes
 * @property-read AssetCategory|null $assetCategory
 * @property-read EloquentCollection<int, AssetAssignment> $assignments
 * @property-read AssetAssignment|null $currentAssignment
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
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

    /**
     * @return BelongsTo<AssetCategory, $this>
     */
    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * @return HasMany<AssetAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)
            ->orderByDesc('assigned_at')
            ->orderByDesc('id');
    }

    /**
     * @return HasOne<AssetAssignment, $this>
     */
    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)
            ->ofMany(['id' => 'max'], function ($query): void {
                $query->whereIn('status', ['assigned', 'issued']);
            });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
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
