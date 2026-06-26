<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property int|null $working_hours_minutes
 * @property int|null $grace_minutes
 * @property int|null $late_after_minutes
 * @property int|null $half_day_minutes
 * @property bool $overtime_eligible
 * @property int|null $overtime_after_minutes
 * @property array<string, mixed>|null $weekend_rule
 * @property bool $work_from_home_allowed
 * @property bool $enforce_geofence
 * @property int|null $allowed_radius_meters
 * @property string $status
 * @property-read Company|null $company
 */
#[Fillable([
    'company_id',
    'name',
    'working_hours_minutes',
    'grace_minutes',
    'late_after_minutes',
    'half_day_minutes',
    'overtime_eligible',
    'overtime_after_minutes',
    'weekend_rule',
    'work_from_home_allowed',
    'enforce_geofence',
    'allowed_radius_meters',
    'status',
])]
class AttendancePolicy extends Model
{
    use BelongsToCompany;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected function casts(): array
    {
        return [
            'overtime_eligible' => 'boolean',
            'weekend_rule' => 'array',
            'work_from_home_allowed' => 'boolean',
            'enforce_geofence' => 'boolean',
        ];
    }
}
