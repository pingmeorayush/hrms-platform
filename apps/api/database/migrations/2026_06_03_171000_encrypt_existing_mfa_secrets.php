<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->select(['id', 'mfa_secret'])
            ->whereNotNull('mfa_secret')
            ->orderBy('id')
            ->chunkById(100, function (Collection $users): void {
                foreach ($users as $user) {
                    if (! is_string($user->mfa_secret) || $user->mfa_secret === '') {
                        continue;
                    }

                    if ($this->isEncrypted($user->mfa_secret)) {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'mfa_secret' => Crypt::encryptString($user->mfa_secret),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Intentionally left blank to avoid writing plaintext MFA secrets back to storage.
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
