<?php

namespace App\Modules\Platform\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class MfaService
{
    public function issueEmailChallenge(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'mfa_email_otp' => Hash::make($code),
            'mfa_email_otp_expires_at' => now()->addMinutes((int) config('platform.auth.mfa_code_minutes')),
        ])->save();

        Mail::raw(
            "Your PhoenixHRMS verification code is {$code}. It expires in ".config('platform.auth.mfa_code_minutes').' minutes.',
            fn ($message) => $message
                ->to($user->email)
                ->subject('Your PhoenixHRMS verification code'),
        );
    }

    public function verify(User $user, string $code): bool
    {
        $method = $this->methodFor($user);

        if ($method === 'authenticator_app') {
            return $user->mfa_secret !== null
                && $this->verifyTotp($user->mfa_secret, $code);
        }

        if (! $user->mfa_email_otp || ! $user->mfa_email_otp_expires_at || $user->mfa_email_otp_expires_at->isPast()) {
            return false;
        }

        $valid = Hash::check($code, $user->mfa_email_otp);

        if ($valid) {
            $user->forceFill([
                'mfa_email_otp' => null,
                'mfa_email_otp_expires_at' => null,
                'mfa_confirmed_at' => now(),
            ])->save();
        }

        return $valid;
    }

    public function methodFor(User $user): string
    {
        return $user->mfa_method ?: 'email_otp';
    }

    private function verifyTotp(string $secret, string $code): bool
    {
        $secretKey = $this->decodeBase32($secret);

        if ($secretKey === '') {
            return false;
        }

        $code = str_pad($code, 6, '0', STR_PAD_LEFT);

        for ($window = -1; $window <= 1; $window++) {
            if (hash_equals($this->generateTotp($secretKey, time() + ($window * 30)), $code)) {
                return true;
            }
        }

        return false;
    }

    private function generateTotp(string $secretKey, int $timestamp): string
    {
        $counter = (int) floor($timestamp / 30);
        $binaryCounter = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        ) % 1_000_000;

        return str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }

    private function decodeBase32(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $normalized = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? '');

        if ($normalized === '') {
            return '';
        }

        $bits = '';

        foreach (str_split($normalized) as $char) {
            $position = strpos($alphabet, $char);

            if ($position === false) {
                return '';
            }

            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }
}
