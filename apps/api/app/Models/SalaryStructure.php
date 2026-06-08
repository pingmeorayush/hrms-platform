<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'previous_version_id',
    'code',
    'name',
    'currency',
    'country_code',
    'pay_frequency',
    'grade',
    'band',
    'level',
    'annual_ctc_amount',
    'basic_salary_amount',
    'gross_salary_amount',
    'net_salary_amount',
    'effective_from',
    'revision_date',
    'version',
    'status',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class SalaryStructure extends Model
{
    use BelongsToCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_version_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(SalaryStructureComponent::class)->orderBy('display_order')->orderBy('id');
    }

    public function employeeCompensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class);
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
            'annual_ctc_amount' => 'decimal:2',
            'basic_salary_amount' => 'decimal:2',
            'gross_salary_amount' => 'decimal:2',
            'net_salary_amount' => 'decimal:2',
            'effective_from' => 'date',
            'revision_date' => 'date',
            'version' => 'integer',
        ];
    }
}
