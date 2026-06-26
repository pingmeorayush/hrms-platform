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
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $department_id
 * @property int|null $location_id
 * @property int|null $manager_id
 * @property int|null $user_id
 * @property Carbon|null $date_of_birth
 * @property Carbon|null $date_of_joining
 * @property Carbon|null $terminated_at
 * @property-read string $full_name
 * @property-read int|null $lifecycle_task_count
 * @property-read int|null $closed_lifecycle_task_count
 * @property-read int|null $incomplete_lifecycle_task_count
 * @property-read Company|null $company
 * @property-read Employee|null $manager
 * @property-read User|null $user
 */
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
    use BelongsToCompany;

    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Designation, $this>
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<CostCenter, $this>
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<EmploymentHistory, $this>
     */
    public function employmentHistories(): HasMany
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    /**
     * @return HasMany<EmployeeBankAccount, $this>
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    /**
     * @return HasMany<EmployeeCompensation, $this>
     */
    public function compensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class)
            ->orderByDesc('effective_from')
            ->orderByDesc('revision_date')
            ->orderByDesc('id');
    }

    /**
     * @return HasMany<Payslip, $this>
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class)
            ->orderByDesc('generated_at')
            ->orderByDesc('id');
    }

    /**
     * @return HasMany<EmployeeDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * @return HasMany<PolicyAcknowledgement, $this>
     */
    public function policyAcknowledgements(): HasMany
    {
        return $this->hasMany(PolicyAcknowledgement::class)
            ->orderByRaw("case when status = 'assigned' then 0 else 1 end")
            ->orderBy('due_date')
            ->orderByDesc('id');
    }

    /**
     * @return HasMany<AssetAssignment, $this>
     */
    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)
            ->orderByDesc('assigned_at')
            ->orderByDesc('id');
    }

    /**
     * @return HasMany<EmployeeOnboardingTask, $this>
     */
    public function lifecycleTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class);
    }

    /**
     * @return HasMany<EmployeeOnboardingTask, $this>
     */
    public function onboardingTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class)
            ->where('lifecycle_type', 'onboarding');
    }

    /**
     * @return HasMany<EmployeeOnboardingTask, $this>
     */
    public function offboardingTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class)
            ->where('lifecycle_type', 'offboarding');
    }

    /**
     * @return HasMany<EmployeeContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(EmployeeContact::class);
    }

    /**
     * @return HasMany<EmployeeAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(EmployeeAddress::class);
    }

    /**
     * @return HasMany<EmployeeEmergencyContact, $this>
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    /**
     * @return Attribute<string, never>
     */
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
