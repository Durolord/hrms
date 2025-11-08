<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Payroll>
 */
final class PayrollFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payroll::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'employee_id' => \App\Models\Employee::factory(),
            'month' => fake()->date(),
            'basic_salary' => fake()->word,
            'status' => fake()->randomElement(['Pending', 'Approved', 'Paid']),
        ];
    }
}
