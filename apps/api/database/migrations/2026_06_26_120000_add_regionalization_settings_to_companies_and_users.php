<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('country_code', 2)->default('IN')->after('currency');
            $table->string('locale', 10)->default('en-IN')->after('country_code');
            $table->string('language', 10)->default('en')->after('locale');
            $table->string('time_format', 10)->default('24h')->after('language');
            $table->json('expansion_country_codes')->nullable()->after('time_format');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('timezone')->nullable()->after('last_login_ip');
            $table->string('currency', 3)->nullable()->after('timezone');
            $table->string('locale', 10)->nullable()->after('currency');
            $table->string('language', 10)->nullable()->after('locale');
            $table->string('time_format', 10)->nullable()->after('language');
        });

        DB::table('companies')->orderBy('id')->get()->each(function (object $company): void {
            $preset = $this->resolvePreset(
                timezone: (string) ($company->timezone ?? 'UTC'),
                currency: (string) ($company->currency ?? 'USD'),
            );

            DB::table('companies')
                ->where('id', $company->id)
                ->update([
                    'country_code' => $preset['country_code'],
                    'locale' => $preset['locale'],
                    'language' => $preset['language'],
                    'time_format' => $preset['time_format'],
                    'expansion_country_codes' => json_encode([]),
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['timezone', 'currency', 'locale', 'language', 'time_format']);
        });

        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['country_code', 'locale', 'language', 'time_format', 'expansion_country_codes']);
        });
    }

    /**
     * @return array{country_code: string, locale: string, language: string, time_format: string}
     */
    private function resolvePreset(string $timezone, string $currency): array
    {
        return match (true) {
            $timezone === 'Asia/Kolkata' || $currency === 'INR' => [
                'country_code' => 'IN',
                'locale' => 'en-IN',
                'language' => 'en',
                'time_format' => '24h',
            ],
            $timezone === 'America/New_York' || $currency === 'USD' => [
                'country_code' => 'US',
                'locale' => 'en-US',
                'language' => 'en',
                'time_format' => '12h',
            ],
            $timezone === 'Europe/London' || $currency === 'GBP' => [
                'country_code' => 'GB',
                'locale' => 'en-GB',
                'language' => 'en',
                'time_format' => '24h',
            ],
            $timezone === 'Europe/Berlin' || $currency === 'EUR' => [
                'country_code' => 'DE',
                'locale' => 'de-DE',
                'language' => 'de',
                'time_format' => '24h',
            ],
            $timezone === 'Asia/Dubai' || $currency === 'AED' => [
                'country_code' => 'AE',
                'locale' => 'en-AE',
                'language' => 'en',
                'time_format' => '12h',
            ],
            $timezone === 'Asia/Singapore' || $currency === 'SGD' => [
                'country_code' => 'SG',
                'locale' => 'en-SG',
                'language' => 'en',
                'time_format' => '24h',
            ],
            default => [
                'country_code' => 'IN',
                'locale' => 'en-IN',
                'language' => 'en',
                'time_format' => '24h',
            ],
        };
    }
};
