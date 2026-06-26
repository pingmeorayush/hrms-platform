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
 * @property string $code
 * @property string $name
 * @property string $category
 * @property string $calculation_type
 * @property float|string|null $flat_amount
 * @property float|string|null $percentage_value
 * @property array<int, string>|null $percentage_basis_component_codes
 * @property string|null $expression_formula
 * @property bool $is_taxable
 * @property bool $is_proratable
 * @property int $display_order
 * @property string $status
 * @property-read Company|null $company
 * @property-read EloquentCollection<int, SalaryStructureComponent> $structureComponents
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'code',
    'name',
    'category',
    'calculation_type',
    'flat_amount',
    'percentage_value',
    'percentage_basis_component_codes',
    'expression_formula',
    'is_taxable',
    'is_proratable',
    'display_order',
    'status',
    'created_by_user_id',
    'updated_by_user_id',
])]
class SalaryComponent extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasMany<SalaryStructureComponent, $this>
     */
    public function structureComponents(): HasMany
    {
        return $this->hasMany(SalaryStructureComponent::class);
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
            'flat_amount' => 'decimal:2',
            'percentage_value' => 'decimal:4',
            'percentage_basis_component_codes' => 'array',
            'is_taxable' => 'boolean',
            'is_proratable' => 'boolean',
            'display_order' => 'integer',
        ];
    }
}
