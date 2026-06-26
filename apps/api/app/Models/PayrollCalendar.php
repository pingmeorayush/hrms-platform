<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $frequency
 * @property string $timezone
 * @property int|null $payroll_day
 * @property int|null $payroll_weekday
 * @property bool $is_default
 * @property string $status
 * @property-read Company|null $company
 * @property-read EloquentCollection<int, PayrollPeriod> $periods
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'name',
    'frequency',
    'timezone',
    'payroll_day',
    'payroll_weekday',
    'is_default',
    'status',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PayrollCalendar extends Model
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
     * @return HasMany<PayrollPeriod, $this>
     */
    public function periods(): HasMany
    {
        return $this->hasMany(PayrollPeriod::class);
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
            'payroll_day' => 'integer',
            'payroll_weekday' => 'integer',
            'is_default' => 'boolean',
        ];
    }
}
