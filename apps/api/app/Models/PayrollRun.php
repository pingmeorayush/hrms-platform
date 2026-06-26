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
 * @property int $payroll_period_id
 * @property string $name
 * @property string $frequency
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $status
 * @property array<string, mixed>|null $prerequisite_snapshot
 * @property array<string, mixed>|null $prerequisite_summary
 * @property array<string, mixed>|null $input_summary
 * @property array<string, mixed>|null $calculation_summary
 * @property Carbon|null $prepared_at
 * @property Carbon|null $inputs_generated_at
 * @property Carbon|null $calculated_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $locked_at
 * @property Carbon|null $reopened_at
 * @property Carbon|null $closed_at
 * @property-read Company|null $company
 * @property-read PayrollPeriod|null $payrollPeriod
 * @property-read EloquentCollection<int, PayrollInput> $inputs
 * @property-read EloquentCollection<int, PayrollAdjustment> $adjustments
 * @property-read EloquentCollection<int, PayrollItem> $items
 * @property-read EloquentCollection<int, Payslip> $payslips
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
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

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<PayrollPeriod, $this>
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * @return HasMany<PayrollInput, $this>
     */
    public function inputs(): HasMany
    {
        return $this->hasMany(PayrollInput::class);
    }

    /**
     * @return HasMany<PayrollAdjustment, $this>
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    /**
     * @return HasMany<PayrollItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    /**
     * @return HasMany<Payslip, $this>
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
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
