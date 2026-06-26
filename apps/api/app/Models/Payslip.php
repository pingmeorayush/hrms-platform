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
 * @property int $payroll_period_id
 * @property int $payroll_item_id
 * @property int $employee_id
 * @property int $employee_compensation_id
 * @property string $slip_number
 * @property string $status
 * @property string $currency
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $payroll_date
 * @property string $file_name
 * @property float|string $gross_salary
 * @property float|string $total_earnings
 * @property float|string $total_deductions
 * @property float|string $net_salary
 * @property float|string $employer_cost
 * @property array<int, array<string, mixed>>|null $earnings_breakdown
 * @property array<int, array<string, mixed>>|null $deductions_breakdown
 * @property array<int, array<string, mixed>>|null $employer_contribution_breakdown
 * @property array<string, mixed>|null $employee_snapshot
 * @property array<string, mixed>|null $company_snapshot
 * @property string $rendered_format
 * @property string $rendered_content
 * @property string $checksum_sha256
 * @property Carbon|null $generated_at
 * @property-read Company|null $company
 * @property-read PayrollRun|null $payrollRun
 * @property-read PayrollPeriod|null $payrollPeriod
 * @property-read PayrollItem|null $payrollItem
 * @property-read Employee|null $employee
 * @property-read EmployeeCompensation|null $employeeCompensation
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'payroll_run_id',
    'payroll_period_id',
    'payroll_item_id',
    'employee_id',
    'employee_compensation_id',
    'slip_number',
    'status',
    'currency',
    'start_date',
    'end_date',
    'payroll_date',
    'file_name',
    'gross_salary',
    'total_earnings',
    'total_deductions',
    'net_salary',
    'employer_cost',
    'earnings_breakdown',
    'deductions_breakdown',
    'employer_contribution_breakdown',
    'employee_snapshot',
    'company_snapshot',
    'rendered_format',
    'rendered_content',
    'checksum_sha256',
    'generated_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class Payslip extends Model
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
     * @return BelongsTo<PayrollPeriod, $this>
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * @return BelongsTo<PayrollItem, $this>
     */
    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
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
            'start_date' => 'date',
            'end_date' => 'date',
            'payroll_date' => 'date',
            'gross_salary' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'employer_cost' => 'decimal:2',
            'earnings_breakdown' => 'array',
            'deductions_breakdown' => 'array',
            'employer_contribution_breakdown' => 'array',
            'employee_snapshot' => 'array',
            'company_snapshot' => 'array',
            'generated_at' => 'datetime',
        ];
    }
}
