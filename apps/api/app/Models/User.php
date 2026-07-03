<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_active
 * @property bool $requires_mfa
 * @property string|null $mfa_method
 * @property string|null $mfa_secret
 * @property string|null $mfa_email_otp
 * @property Carbon|null $mfa_email_otp_expires_at
 * @property Carbon|null $mfa_confirmed_at
 * @property int $failed_login_attempts
 * @property Carbon|null $locked_until
 * @property Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property string|null $timezone
 * @property string|null $currency
 * @property string|null $locale
 * @property string|null $language
 * @property string|null $time_format
 * @property-read Company|null $company
 * @property-read Employee|null $employee
 */
#[Fillable([
    'company_id',
    'name',
    'email',
    'password',
    'is_active',
    'requires_mfa',
    'mfa_method',
    'mfa_secret',
    'mfa_email_otp',
    'mfa_email_otp_expires_at',
    'mfa_confirmed_at',
    'failed_login_attempts',
    'locked_until',
    'last_login_at',
    'last_login_ip',
    'timezone',
    'currency',
    'locale',
    'language',
    'time_format',
])]
#[Hidden(['password', 'remember_token', 'mfa_secret', 'mfa_email_otp'])]
class User extends Authenticatable
{
    use BelongsToCompany;
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    protected string $guard_name = 'web';

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasOne<Employee, $this>
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * @return Attribute<string, never>
     */
    public function initials(): Attribute
    {
        return Attribute::make(
            get: fn (): string => collect(explode(' ', $this->name))
                ->filter()
                ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
                ->take(2)
                ->implode(''),
        );
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'requires_mfa' => 'boolean',
            'mfa_secret' => 'encrypted',
            'mfa_email_otp_expires_at' => 'datetime',
            'mfa_confirmed_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
