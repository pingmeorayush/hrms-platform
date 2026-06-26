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
 * @property int $employee_id
 * @property int $salary_structure_id
 * @property int|null $previous_revision_id
 * @property string $salary_structure_code
 * @property int $salary_structure_version
 * @property string $currency
 * @property string $pay_frequency
 * @property float|string $annual_ctc_amount
 * @property float|string $basic_salary_amount
 * @property float|string $gross_salary_amount
 * @property float|string $net_salary_amount
 * @property string $revision_reason
 * @property Carbon|null $effective_from
 * @property Carbon|null $revision_date
 * @property string|null $notes
 * @property array<int, array<string, mixed>>|null $component_snapshot
 * @property-read Company|null $company
 * @property-read Employee|null $employee
 * @property-read SalaryStructure|null $salaryStructure
 * @property-read EmployeeCompensation|null $previousRevision
 * @property-read EloquentCollection<int, EmployeeCompensation> $nextRevisions
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'employee_id',
    'salary_structure_id',
    'previous_revision_id',
    'salary_structure_code',
    'salary_structure_version',
    'currency',
    'pay_frequency',
    'annual_ctc_amount',
    'basic_salary_amount',
    'gross_salary_amount',
    'net_salary_amount',
    'revision_reason',
    'effective_from',
    'revision_date',
    'notes',
    'component_snapshot',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeCompensation extends Model
{
    use BelongsToCompany;

    protected $table = 'employee_compensations';

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<SalaryStructure, $this>
     */
    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function previousRevision(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_revision_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function nextRevisions(): HasMany
    {
        return $this->hasMany(self::class, 'previous_revision_id');
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
            'salary_structure_version' => 'integer',
            'annual_ctc_amount' => 'decimal:2',
            'basic_salary_amount' => 'decimal:2',
            'gross_salary_amount' => 'decimal:2',
            'net_salary_amount' => 'decimal:2',
            'effective_from' => 'date',
            'revision_date' => 'date',
            'component_snapshot' => 'array',
        ];
    }
}
