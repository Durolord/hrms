<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PayScale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PayScale>
 */
final class PayScaleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PayScale::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'basic_salary' => fake()->word,
            'active' => fake()->randomNumber(1),
        ];
    }
}
