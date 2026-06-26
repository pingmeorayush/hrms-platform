<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $salary_structure_id
 * @property int $salary_component_id
 * @property int $display_order
 * @property float|string|null $configured_amount
 * @property float|string|null $configured_percentage
 * @property array<int, string>|null $configured_basis_component_codes
 * @property string|null $configured_expression_formula
 * @property-read Company|null $company
 * @property-read SalaryStructure|null $salaryStructure
 * @property-read SalaryComponent|null $salaryComponent
 */
#[Fillable([
    'company_id',
    'salary_structure_id',
    'salary_component_id',
    'display_order',
    'configured_amount',
    'configured_percentage',
    'configured_basis_component_codes',
    'configured_expression_formula',
])]
class SalaryStructureComponent extends Model
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
     * @return BelongsTo<SalaryStructure, $this>
     */
    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    /**
     * @return BelongsTo<SalaryComponent, $this>
     */
    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'configured_amount' => 'decimal:2',
            'configured_percentage' => 'decimal:4',
            'configured_basis_component_codes' => 'array',
        ];
    }
}
