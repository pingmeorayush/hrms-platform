<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_access_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password@12345'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password@12345',
            'device_name' => 'browser',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'event_type' => 'auth.login.succeeded',
        ]);
    }

    public function test_failed_logins_lock_the_account_after_the_configured_threshold(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password@12345'),
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'WrongPassword@12345',
            ])->assertStatus(422);
        }

        $user->refresh();

        $this->assertNotNull($user->locked_until);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password@12345',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'This account is temporarily locked.');
    }

    public function test_forgot_password_creates_a_reset_token_and_audits_the_request(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ])->assertOk();

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'auth.password_reset.requested',
        ]);
    }

    public function test_password_reset_updates_credentials_and_revokes_existing_tokens(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password@12345'),
        ]);

        $token = $user->createToken('existing-session');
        $resetToken = Password::broker()->createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $resetToken,
            'password' => 'NewPassword@12345',
            'password_confirmation' => 'NewPassword@12345',
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'NewPassword@12345',
        ])->assertOk();
    }

    public function test_user_can_complete_an_authenticator_app_mfa_challenge(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';

        $user = User::factory()->create([
            'password' => Hash::make('Password@12345'),
            'requires_mfa' => true,
            'mfa_method' => 'authenticator_app',
            'mfa_secret' => $secret,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password@12345',
        ])->assertOk()
            ->assertJsonPath('data.mfa_required', true)
            ->assertJsonPath('data.mfa_method', 'authenticator_app');

        $code = $this->generateTotp($secret);

        $this->postJson('/api/v1/auth/verify-mfa', [
            'email' => $user->email,
            'code' => $code,
            'device_name' => 'browser',
        ])->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer');
    }

    public function test_login_with_email_otp_mfa_issues_a_challenge_and_audit_log(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'password' => Hash::make('Password@12345'),
            'requires_mfa' => true,
            'mfa_method' => 'email_otp',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password@12345',
            'device_name' => 'browser',
        ])->assertOk()
            ->assertJsonPath('data.mfa_required', true)
            ->assertJsonPath('data.mfa_method', 'email_otp');

        $user->refresh();

        $this->assertNotNull($user->mfa_email_otp);
        $this->assertNotNull($user->mfa_email_otp_expires_at);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'auth.mfa.challenge_issued',
        ]);
    }

    public function test_user_can_complete_an_email_otp_mfa_challenge(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password@12345'),
            'requires_mfa' => true,
            'mfa_method' => 'email_otp',
            'mfa_email_otp' => Hash::make('123456'),
            'mfa_email_otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/api/v1/auth/verify-mfa', [
            'email' => $user->email,
            'code' => '123456',
            'device_name' => 'browser',
        ])->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer');

        $user->refresh();

        $this->assertNull($user->mfa_email_otp);
        $this->assertNotNull($user->mfa_confirmed_at);
    }

    public function test_user_can_logout_and_revoke_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('browser');

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_expired_access_tokens_cannot_access_protected_routes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('browser', ['*'], now()->subMinute());

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    private function generateTotp(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $normalized = strtoupper($secret);
        $bits = '';

        foreach (str_split($normalized) as $char) {
            $bits .= str_pad((string) decbin(strpos($alphabet, $char)), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        $counter = (int) floor(time() / 30);
        $binaryCounter = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $decoded, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        ) % 1_000_000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }
}
