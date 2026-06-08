<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'employee_id',
    'shift_id',
    'shift_roster_id',
    'attendance_date',
    'check_in_at',
    'check_in_channel',
    'check_in_ip_address',
    'check_in_user_agent',
    'check_in_metadata',
    'check_out_at',
    'check_out_channel',
    'check_out_ip_address',
    'check_out_user_agent',
    'check_out_metadata',
    'worked_minutes',
    'primary_status',
    'scheduled_start_at',
    'scheduled_end_at',
    'scheduled_work_minutes',
    'break_duration_minutes',
    'is_late',
    'late_minutes',
    'is_half_day',
    'overtime_minutes',
    'is_weekend',
    'is_holiday',
    'holiday_name',
    'is_early_departure',
    'early_departure_minutes',
    'calculated_at',
    'calculation_metadata',
    'created_by_user_id',
    'updated_by_user_id',
])]
class AttendanceRecord extends Model
{
    use BelongsToCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function shiftRoster(): BelongsTo
    {
        return $this->belongsTo(ShiftRoster::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_at' => 'datetime',
            'check_in_metadata' => 'array',
            'check_out_at' => 'datetime',
            'check_out_metadata' => 'array',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'is_late' => 'boolean',
            'is_half_day' => 'boolean',
            'is_weekend' => 'boolean',
            'is_holiday' => 'boolean',
            'is_early_departure' => 'boolean',
            'calculated_at' => 'datetime',
            'calculation_metadata' => 'array',
        ];
    }
}
