<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Allowance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Allowance>
 */
final class AllowanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Allowance::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'pay_scale_id' => \App\Models\PayScale::factory(),
            'amount' => fake()->word,
            'is_percentage' => fake()->randomNumber(1),
            'reason' => fake()->optional()->word,
            'employee_id' => \App\Models\Employee::factory(),
        ];
    }
}
