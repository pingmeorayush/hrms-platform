<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $payroll_run_id
 * @property int $employee_id
 * @property int $employee_compensation_id
 * @property string $status
 * @property float|string $employment_days
 * @property float|string $unpaid_days
 * @property float|string $lop_days
 * @property int $overtime_minutes
 * @property float|string $overtime_earnings
 * @property float|string $gross_salary
 * @property float|string $total_earnings
 * @property float|string $total_deductions
 * @property float|string $net_salary
 * @property float|string $employer_cost
 * @property array<int, array<string, mixed>>|null $earnings_breakdown
 * @property array<int, array<string, mixed>>|null $deductions_breakdown
 * @property array<int, array<string, mixed>>|null $employer_contribution_breakdown
 * @property array<string, mixed>|null $input_snapshot
 * @property array<int, string>|null $validation_errors
 * @property-read Company|null $company
 * @property-read PayrollRun|null $payrollRun
 * @property-read Employee|null $employee
 * @property-read EmployeeCompensation|null $employeeCompensation
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'payroll_run_id',
    'employee_id',
    'employee_compensation_id',
    'status',
    'employment_days',
    'unpaid_days',
    'lop_days',
    'overtime_minutes',
    'overtime_earnings',
    'gross_salary',
    'total_earnings',
    'total_deductions',
    'net_salary',
    'employer_cost',
    'earnings_breakdown',
    'deductions_breakdown',
    'employer_contribution_breakdown',
    'input_snapshot',
    'validation_errors',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PayrollItem extends Model
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
            'employment_days' => 'decimal:2',
            'unpaid_days' => 'decimal:2',
            'lop_days' => 'decimal:2',
            'overtime_minutes' => 'integer',
            'overtime_earnings' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'employer_cost' => 'decimal:2',
            'earnings_breakdown' => 'array',
            'deductions_breakdown' => 'array',
            'employer_contribution_breakdown' => 'array',
            'input_snapshot' => 'array',
            'validation_errors' => 'array',
        ];
    }
}
