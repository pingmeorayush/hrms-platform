<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'status' => 'active',
            'subscription_plan' => fake()->randomElement(['starter', 'professional', 'enterprise']),
            'timezone' => fake()->randomElement(['UTC', 'Asia/Kolkata', 'Europe/Berlin', 'America/New_York']),
            'currency' => fake()->randomElement(['USD', 'INR', 'EUR']),
        ];
    }
}
