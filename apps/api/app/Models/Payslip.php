<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeCompensation(): BelongsTo
    {
        return $this->belongsTo(EmployeeCompensation::class);
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
