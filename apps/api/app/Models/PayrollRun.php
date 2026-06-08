<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'payroll_period_id',
    'name',
    'frequency',
    'start_date',
    'end_date',
    'status',
    'prerequisite_snapshot',
    'prerequisite_summary',
    'input_summary',
    'calculation_summary',
    'prepared_at',
    'inputs_generated_at',
    'calculated_at',
    'approved_at',
    'locked_at',
    'reopened_at',
    'closed_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PayrollRun extends Model
{
    use BelongsToCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(PayrollInput::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
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
            'prerequisite_snapshot' => 'array',
            'prerequisite_summary' => 'array',
            'input_summary' => 'array',
            'calculation_summary' => 'array',
            'prepared_at' => 'datetime',
            'inputs_generated_at' => 'datetime',
            'calculated_at' => 'datetime',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
            'reopened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
