<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'employee_code' => strtoupper(fake()->unique()->bothify('EMP#####')),
            'first_name' => fake()->firstName(),
            'middle_name' => null,
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'date_of_birth' => fake()->dateTimeBetween('-45 years', '-21 years'),
            'gender' => fake()->randomElement(['male', 'female', 'non_binary']),
            'marital_status' => fake()->randomElement(['single', 'married']),
            'date_of_joining' => fake()->dateTimeBetween('-5 years', 'now'),
            'employment_type' => fake()->randomElement(['full_time', 'contract']),
            'employment_status' => 'active',
            'department_id' => fn (array $attributes) => Department::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'designation_id' => fn (array $attributes) => Designation::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'manager_id' => null,
            'location_id' => fn (array $attributes) => Location::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'cost_center_id' => null,
            'user_id' => null,
            'termination_reason' => null,
            'terminated_at' => null,
        ];
    }
}
