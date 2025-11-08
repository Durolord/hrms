<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Leave;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Leave>
 */
final class LeaveFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Leave::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'approved_on' => fake()->optional()->date(),
            'status' => fake()->randomElement(['Pending', 'Approved', 'Rejected']),
            'deducted_from_payroll' => fake()->randomNumber(1),
            'max_days' => fake()->randomNumber(),
            'employee_id' => \App\Models\Employee::factory(),
            'leave_type_id' => \App\Models\LeaveType::factory(),
            'approver_id' => \App\Models\User::factory(),
            'line_manager_id' => \App\Models\Employee::factory(),
        ];
    }
}
