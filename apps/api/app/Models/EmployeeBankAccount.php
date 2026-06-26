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
 * @property string $status
 * @property bool $is_primary
 * @property Carbon|null $verified_at
 * @property string|null $notes
 * @property-read Employee|null $employee
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
#[Fillable([
    'company_id',
    'employee_id',
    'account_holder_name',
    'bank_name',
    'branch_name',
    'account_number',
    'ifsc_code',
    'routing_number',
    'iban',
    'swift_code',
    'status',
    'is_primary',
    'verified_at',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeBankAccount extends Model
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
            'account_holder_name' => 'encrypted',
            'bank_name' => 'encrypted',
            'branch_name' => 'encrypted',
            'account_number' => 'encrypted',
            'ifsc_code' => 'encrypted',
            'routing_number' => 'encrypted',
            'iban' => 'encrypted',
            'swift_code' => 'encrypted',
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
}
