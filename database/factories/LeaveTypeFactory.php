<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\LeaveType>
 */
final class LeaveTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LeaveType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'description' => fake()->optional()->text,
            'deduction_amount' => fake()->word,
            'max_days' => fake()->randomNumber(),
            'is_percentage' => fake()->randomNumber(1),
        ];
    }
}
