<?php

namespace App\Modules\Platform\Auth\Services;

use App\Models\User;
use App\Modules\Platform\Audit\Services\AuditLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly MfaService $mfaService,
    ) {}

    public function login(string $email, string $password, string $deviceName, ?string $ipAddress, ?string $userAgent): array
    {
        $user = User::withoutGlobalScopes()
            ->with('company')
            ->where('email', $email)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $this->guardAgainstLockedAccount($user);
        $this->guardAgainstInactiveTenant($user);

        if (! Hash::check($password, $user->password)) {
            $this->recordFailedAttempt($user, 'auth.login.failed', $ipAddress, $userAgent);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->requires_mfa) {
            if ($this->mfaService->methodFor($user) === 'email_otp') {
                $this->mfaService->issueEmailChallenge($user);
            }

            $this->auditLogger->record(
                eventType: 'auth.mfa.challenge_issued',
                actor: $user,
                metadata: ['method' => $this->mfaService->methodFor($user)],
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return [
                'mfa_required' => true,
                'mfa_method' => $this->mfaService->methodFor($user),
            ];
        }

        return $this->completeAuthentication($user, $deviceName, $ipAddress, $userAgent);
    }

    public function verifyMfa(string $email, string $code, string $deviceName, ?string $ipAddress, ?string $userAgent): array
    {
        $user = User::withoutGlobalScopes()
            ->with('company')
            ->where('email', $email)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['The MFA challenge is invalid.'],
            ]);
        }

        $this->guardAgainstLockedAccount($user);
        $this->guardAgainstInactiveTenant($user);

        if (! $this->mfaService->verify($user, $code)) {
            $this->recordFailedAttempt($user, 'auth.mfa.failed', $ipAddress, $userAgent);

            throw ValidationException::withMessages([
                'code' => ['The MFA code is invalid or expired.'],
            ]);
        }

        return $this->completeAuthentication($user, $deviceName, $ipAddress, $userAgent);
    }

    public function logout(User $user, ?string $ipAddress, ?string $userAgent): void
    {
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        $this->auditLogger->record(
            eventType: 'auth.logout.succeeded',
            actor: $user,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
    }

    public function sendPasswordResetLink(string $email, ?string $ipAddress, ?string $userAgent): void
    {
        Password::broker()->sendResetLink(['email' => $email]);

        $user = User::withoutGlobalScopes()->where('email', $email)->first();

        if ($user) {
            $this->auditLogger->record(
                eventType: 'auth.password_reset.requested',
                actor: $user,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );
        }
    }

    public function resetPassword(array $payload, ?string $ipAddress, ?string $userAgent): void
    {
        $status = Password::broker()->reset(
            $payload,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        $user = User::withoutGlobalScopes()->where('email', $payload['email'])->first();

        if ($user) {
            $this->auditLogger->record(
                eventType: 'auth.password_reset.completed',
                actor: $user,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );
        }
    }

    private function completeAuthentication(User $user, string $deviceName, ?string $ipAddress, ?string $userAgent): array
    {
        $expiresAt = now()->addMinutes((int) config('platform.auth.session_timeout_minutes'));
        $plainTextToken = $user->createToken(
            config('platform.auth.token_name').':'.$deviceName,
            ['*'],
            $expiresAt,
        )->plainTextToken;

        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ])->save();

        $this->auditLogger->record(
            eventType: 'auth.login.succeeded',
            actor: $user,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return [
            'access_token' => $plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    private function guardAgainstLockedAccount(User $user): void
    {
        if ($user->locked_until && $user->locked_until->isFuture()) {
            throw ValidationException::withMessages([
                'email' => ['This account is temporarily locked.'],
            ]);
        }
    }

    private function guardAgainstInactiveTenant(User $user): void
    {
        if (! $user->is_active || ! $user->company || ! $user->company->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['This account does not belong to an active tenant.'],
            ]);
        }
    }

    private function recordFailedAttempt(User $user, string $eventType, ?string $ipAddress, ?string $userAgent): void
    {
        $attempts = $user->failed_login_attempts + 1;
        $lockoutMinutes = (int) config('platform.auth.lockout_minutes');
        $maxAttempts = (int) config('platform.auth.max_login_attempts');

        $user->forceFill([
            'failed_login_attempts' => $attempts,
            'locked_until' => $attempts >= $maxAttempts ? now()->addMinutes($lockoutMinutes) : null,
        ])->save();

        $this->auditLogger->record(
            eventType: $eventType,
            actor: $user,
            metadata: [
                'failed_login_attempts' => $attempts,
                'locked' => $attempts >= $maxAttempts,
            ],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
    }
}
