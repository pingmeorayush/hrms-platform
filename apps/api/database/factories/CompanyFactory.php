<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();
        $configuredCountries = collect((array) config('regionalization.countries', []))
            ->map(fn (array $preset, string $code): array => $preset + ['code' => $code])
            ->values();
        $preset = $configuredCountries->isNotEmpty()
            ? $configuredCountries->random()
            : [
                'code' => 'IN',
                'locale' => 'en-IN',
                'language' => 'en',
                'timezone' => 'Asia/Kolkata',
                'currency' => 'INR',
                'time_format' => '24h',
            ];
        $expansionCountryCodes = $configuredCountries
            ->pluck('code')
            ->reject(fn (string $code): bool => $code === $preset['code'])
            ->take(2)
            ->values()
            ->all();

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'status' => 'active',
            'subscription_plan' => fake()->randomElement(['starter', 'professional', 'enterprise']),
            'timezone' => $preset['timezone'],
            'currency' => $preset['currency'],
            'country_code' => $preset['code'],
            'locale' => $preset['locale'],
            'language' => $preset['language'],
            'time_format' => $preset['time_format'],
            'expansion_country_codes' => $expansionCountryCodes,
        ];
    }
}
