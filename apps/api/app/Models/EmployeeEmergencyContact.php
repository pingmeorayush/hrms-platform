<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property string $name
 * @property string $relationship
 * @property string $phone_number
 * @property string|null $email
 * @property string|null $address
 * @property int $priority
 * @property string|null $notes
 * @property-read Employee|null $employee
 */
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

    /**
     * @return BelongsTo<Employee, $this>
     */
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
