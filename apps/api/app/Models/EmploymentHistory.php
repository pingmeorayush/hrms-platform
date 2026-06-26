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
 * @property string $action
 * @property Carbon|null $effective_date
 * @property int|null $previous_department_id
 * @property int|null $department_id
 * @property int|null $previous_designation_id
 * @property int|null $designation_id
 * @property int|null $previous_manager_id
 * @property int|null $manager_id
 * @property int|null $previous_location_id
 * @property int|null $location_id
 * @property string|null $previous_employment_status
 * @property string $employment_status
 * @property int|null $changed_by_user_id
 * @property string|null $notes
 * @property array<string, mixed>|null $metadata
 * @property-read Employee|null $employee
 * @property-read User|null $actor
 */
#[Fillable([
    'company_id',
    'employee_id',
    'action',
    'effective_date',
    'previous_department_id',
    'department_id',
    'previous_designation_id',
    'designation_id',
    'previous_manager_id',
    'manager_id',
    'previous_location_id',
    'location_id',
    'previous_employment_status',
    'employment_status',
    'changed_by_user_id',
    'notes',
    'metadata',
])]
class EmploymentHistory extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'metadata' => 'array',
        ];
    }
}
