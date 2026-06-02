<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

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
