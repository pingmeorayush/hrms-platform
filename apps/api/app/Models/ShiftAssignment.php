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
 * @property int $shift_id
 * @property string $assignment_type
 * @property int|null $employee_id
 * @property int|null $department_id
 * @property int|null $location_id
 * @property Carbon|null $effective_from
 * @property Carbon|null $effective_to
 * @property string|null $notes
 * @property string $status
 * @property int|null $created_by_user_id
 * @property-read Company|null $company
 * @property-read Shift|null $shift
 * @property-read Employee|null $employee
 * @property-read Department|null $department
 * @property-read Location|null $location
 * @property-read User|null $createdBy
 */
#[Fillable([
    'company_id',
    'shift_id',
    'assignment_type',
    'employee_id',
    'department_id',
    'location_id',
    'effective_from',
    'effective_to',
    'notes',
    'status',
    'created_by_user_id',
])]
class ShiftAssignment extends Model
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
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }
}
