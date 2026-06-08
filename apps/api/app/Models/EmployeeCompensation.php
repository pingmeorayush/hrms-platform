<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    public function previousRevision(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_revision_id');
    }

    public function nextRevisions(): HasMany
    {
        return $this->hasMany(self::class, 'previous_revision_id');
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
