<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function structureComponents(): HasMany
    {
        return $this->hasMany(SalaryStructureComponent::class);
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
            'flat_amount' => 'decimal:2',
            'percentage_value' => 'decimal:4',
            'percentage_basis_component_codes' => 'array',
            'is_taxable' => 'boolean',
            'is_proratable' => 'boolean',
            'display_order' => 'integer',
        ];
    }
}
