<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $payroll_calendar_id
 * @property string $name
 * @property string $frequency
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $payroll_date
 * @property string $status
 * @property Carbon|null $opened_at
 * @property Carbon|null $prepared_at
 * @property Carbon|null $closed_at
 * @property-read Company|null $company
 * @property-read PayrollCalendar|null $payrollCalendar
 * @property-read EloquentCollection<int, PayrollRun> $runs
 * @property-read PayrollRun|null $latestRun
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'payroll_calendar_id',
    'name',
    'frequency',
    'start_date',
    'end_date',
    'payroll_date',
    'status',
    'opened_at',
    'prepared_at',
    'closed_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PayrollPeriod extends Model
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
     * @return BelongsTo<PayrollCalendar, $this>
     */
    public function payrollCalendar(): BelongsTo
    {
        return $this->belongsTo(PayrollCalendar::class);
    }

    /**
     * @return HasMany<PayrollRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    /**
     * @return HasOne<PayrollRun, $this>
     */
    public function latestRun(): HasOne
    {
        return $this->hasOne(PayrollRun::class)->latestOfMany();
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
            'opened_at' => 'datetime',
            'prepared_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
