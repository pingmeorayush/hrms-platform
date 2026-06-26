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
 * @property string $type
 * @property string|null $label
 * @property string $value
 * @property bool $is_primary
 * @property string $status
 * @property string|null $notes
 * @property-read Employee|null $employee
 */
#[Fillable([
    'company_id',
    'employee_id',
    'type',
    'label',
    'value',
    'is_primary',
    'status',
    'notes',
    'created_by_user_id',
    'updated_by_user_id',
])]
class EmployeeContact extends Model
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
            'is_primary' => 'boolean',
        ];
    }
}
