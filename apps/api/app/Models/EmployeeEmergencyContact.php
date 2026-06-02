<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'name',
    'relationship',
    'phone_number',
    'email',
    'address',
    'priority',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeEmergencyContact extends Model
{
    use BelongsToCompany;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }
}
