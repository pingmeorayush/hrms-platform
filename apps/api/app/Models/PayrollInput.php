<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeCompensation(): BelongsTo
    {
        return $this->belongsTo(EmployeeCompensation::class);
    }

    public function payrollAdjustment(): BelongsTo
    {
        return $this->belongsTo(PayrollAdjustment::class);
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
            'quantity' => 'decimal:2',
            'amount' => 'decimal:2',
            'effective_date' => 'date',
            'source_record_id' => 'integer',
            'metadata' => 'array',
        ];
    }
}
