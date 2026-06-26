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
 * @property int $employee_id
 * @property int $leave_type_id
 * @property int $leave_policy_id
 * @property int $policy_version
 * @property float $available_days
 * @property float $booked_days
 * @property float $used_days
 * @property float $accrued_days
 * @property float $carry_forward_days
 * @property float $projected_encashable_days
 * @property Carbon|null $current_period_start
 * @property Carbon|null $current_period_end
 * @property string|null $last_calculation_hash
 * @property string $status
 * @property-read Company|null $company
 * @property-read Employee|null $employee
 * @property-read LeaveType|null $leaveType
 * @property-read LeavePolicy|null $leavePolicy
 * @property-read EloquentCollection<int, LeaveBalanceEntry> $entries
 */
#[Fillable([
    'company_id',
    'employee_id',
    'leave_type_id',
    'leave_policy_id',
    'policy_version',
    'available_days',
    'booked_days',
    'used_days',
    'accrued_days',
    'carry_forward_days',
    'projected_encashable_days',
    'current_period_start',
    'current_period_end',
    'last_calculation_hash',
    'status',
])]
class LeaveBalance extends Model
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
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<LeaveType, $this>
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * @return BelongsTo<LeavePolicy, $this>
     */
    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class);
    }

    /**
     * @return HasMany<LeaveBalanceEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(LeaveBalanceEntry::class)->orderByDesc('effective_on')->orderByDesc('id');
    }

    protected function casts(): array
    {
        return [
            'available_days' => 'float',
            'booked_days' => 'float',
            'used_days' => 'float',
            'accrued_days' => 'float',
            'carry_forward_days' => 'float',
            'projected_encashable_days' => 'float',
            'current_period_start' => 'date',
            'current_period_end' => 'date',
        ];
    }
}
