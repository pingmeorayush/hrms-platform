<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Designation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Designation>
 */
class DesignationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => strtoupper(fake()->unique()->bothify('DSG###')),
            'name' => fake()->unique()->jobTitle(),
            'description' => fake()->sentence(),
            'status' => 'active',
        ];
    }
}
