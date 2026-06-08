<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
