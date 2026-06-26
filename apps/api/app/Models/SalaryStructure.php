<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $previous_version_id
 * @property string $code
 * @property string $name
 * @property string $currency
 * @property string|null $country_code
 * @property string $pay_frequency
 * @property string|null $grade
 * @property string|null $band
 * @property string|null $level
 * @property float|string $annual_ctc_amount
 * @property float|string $basic_salary_amount
 * @property float|string $gross_salary_amount
 * @property float|string $net_salary_amount
 * @property Carbon|null $effective_from
 * @property Carbon|null $revision_date
 * @property int $version
 * @property string $status
 * @property string|null $notes
 * @property-read Company|null $company
 * @property-read SalaryStructure|null $previousVersion
 * @property-read EloquentCollection<int, SalaryStructureComponent> $components
 * @property-read EloquentCollection<int, EmployeeCompensation> $employeeCompensations
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
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

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_version_id');
    }

    /**
     * @return HasMany<SalaryStructureComponent, $this>
     */
    public function components(): HasMany
    {
        return $this->hasMany(SalaryStructureComponent::class)->orderBy('display_order')->orderBy('id');
    }

    /**
     * @return HasMany<EmployeeCompensation, $this>
     */
    public function employeeCompensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class);
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
