<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bonus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Bonus>
 */
final class BonusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bonus::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'employee_id' => \App\Models\Employee::factory(),
            'amount' => fake()->word,
            'month' => fake()->date(),
            'reason' => fake()->text,
            'is_percentage' => fake()->randomNumber(1),
        ];
    }
}
