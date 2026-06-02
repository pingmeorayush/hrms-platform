<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'type',
    'address_line_1',
    'address_line_2',
    'city',
    'state',
    'country',
    'postal_code',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeAddress extends Model
{
    use BelongsToCompany;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
