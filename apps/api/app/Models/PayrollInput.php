<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $payroll_run_id
 * @property int $employee_id
 * @property int|null $employee_compensation_id
 * @property int|null $payroll_adjustment_id
 * @property string $source_type
 * @property string $input_code
 * @property string $unit
 * @property float|string|null $quantity
 * @property float|string|null $amount
 * @property Carbon|null $effective_date
 * @property int|null $source_record_id
 * @property array<string, mixed>|null $metadata
 * @property-read Company|null $company
 * @property-read PayrollRun|null $payrollRun
 * @property-read Employee|null $employee
 * @property-read EmployeeCompensation|null $employeeCompensation
 * @property-read PayrollAdjustment|null $payrollAdjustment
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'payroll_run_id',
    'employee_id',
    'employee_compensation_id',
    'payroll_adjustment_id',
    'source_type',
    'input_code',
    'unit',
    'quantity',
    'amount',
    'effective_date',
    'source_record_id',
    'metadata',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PayrollInput extends Model
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
     * @return BelongsTo<PayrollRun, $this>
     */
    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<EmployeeCompensation, $this>
     */
    public function employeeCompensation(): BelongsTo
    {
        return $this->belongsTo(EmployeeCompensation::class);
    }

    /**
     * @return BelongsTo<PayrollAdjustment, $this>
     */
    public function payrollAdjustment(): BelongsTo
    {
        return $this->belongsTo(PayrollAdjustment::class);
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
            'quantity' => 'decimal:2',
            'amount' => 'decimal:2',
            'effective_date' => 'date',
            'source_record_id' => 'integer',
            'metadata' => 'array',
        ];
    }
}
