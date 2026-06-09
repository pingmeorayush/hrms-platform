<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'employee_code',
    'first_name',
    'middle_name',
    'last_name',
    'email',
    'phone',
    'date_of_birth',
    'gender',
    'marital_status',
    'date_of_joining',
    'employment_type',
    'employment_status',
    'department_id',
    'designation_id',
    'manager_id',
    'location_id',
    'cost_center_id',
    'user_id',
    'termination_reason',
    'terminated_at',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use BelongsToCompany;

    use HasFactory;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employmentHistories(): HasMany
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    public function compensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class)
            ->orderByDesc('effective_from')
            ->orderByDesc('revision_date')
            ->orderByDesc('id');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class)
            ->orderByDesc('generated_at')
            ->orderByDesc('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function policyAcknowledgements(): HasMany
    {
        return $this->hasMany(PolicyAcknowledgement::class)
            ->orderByRaw("case when status = 'assigned' then 0 else 1 end")
            ->orderBy('due_date')
            ->orderByDesc('id');
    }

    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)
            ->orderByDesc('assigned_at')
            ->orderByDesc('id');
    }

    public function lifecycleTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class);
    }

    public function onboardingTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class)
            ->where('lifecycle_type', 'onboarding');
    }

    public function offboardingTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class)
            ->where('lifecycle_type', 'offboarding');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(EmployeeContact::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(EmployeeAddress::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => collect([$this->first_name, $this->middle_name, $this->last_name])
                ->filter()
                ->implode(' '),
        );
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_joining' => 'date',
            'terminated_at' => 'datetime',
        ];
    }
}
