<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Deduction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Deduction>
 */
final class DeductionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Deduction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'employee_id' => \App\Models\Employee::factory(),
            'amount' => fake()->word,
            'month' => fake()->date(),
            'is_percentage' => fake()->randomNumber(1),
            'reason' => fake()->text,
        ];
    }
}
