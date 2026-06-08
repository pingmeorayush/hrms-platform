<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

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
