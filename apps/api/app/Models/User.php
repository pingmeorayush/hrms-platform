<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

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
])]
#[Hidden(['password', 'remember_token', 'mfa_secret', 'mfa_email_otp'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToCompany;

    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected string $guard_name = 'web';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

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
            'mfa_email_otp_expires_at' => 'datetime',
            'mfa_confirmed_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
