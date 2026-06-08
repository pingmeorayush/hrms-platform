<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company_id',
    'payroll_calendar_id',
    'name',
    'frequency',
    'start_date',
    'end_date',
    'payroll_date',
    'status',
    'opened_at',
    'prepared_at',
    'closed_at',
    'created_by_user_id',
    'updated_by_user_id',
])]
class PayrollPeriod extends Model
{
    use BelongsToCompany;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrollCalendar(): BelongsTo
    {
        return $this->belongsTo(PayrollCalendar::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    public function latestRun(): HasOne
    {
        return $this->hasOne(PayrollRun::class)->latestOfMany();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'payroll_date' => 'date',
            'opened_at' => 'datetime',
            'prepared_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
