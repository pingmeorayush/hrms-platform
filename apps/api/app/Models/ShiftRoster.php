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
 * @property int $employee_id
 * @property int $shift_id
 * @property Carbon|null $work_date
 * @property string|null $notes
 * @property string $status
 * @property int|null $created_by_user_id
 * @property-read Company|null $company
 * @property-read Employee|null $employee
 * @property-read Shift|null $shift
 * @property-read User|null $createdBy
 */
#[Fillable([
    'company_id',
    'employee_id',
    'shift_id',
    'work_date',
    'notes',
    'status',
    'created_by_user_id',
])]
class ShiftRoster extends Model
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
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
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
            'work_date' => 'date',
        ];
    }
}
