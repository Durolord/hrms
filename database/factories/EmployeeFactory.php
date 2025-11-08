<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Employee>
 */
final class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'email' => fake()->safeEmail,
            'phone' => fake()->optional()->phoneNumber,
            'employment_start_date' => fake()->date(),
            'active' => fake()->randomNumber(1),
            'user_id' => \App\Models\User::factory(),
            'department_id' => \App\Models\Department::factory(),
            'designation_id' => \App\Models\Designation::factory(),
            'branch_id' => \App\Models\Branch::factory(),
            'manager_id' => \App\Models\Employee::factory(),
            'bank_id' => \App\Models\Bank::factory(),
            'account_number' => fake()->optional()->word,
            'pay_scale_id' => \App\Models\PayScale::factory(),
        ];
    }
}
