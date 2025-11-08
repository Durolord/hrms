<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Opening;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Opening>
 */
final class OpeningFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Opening::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title,
            'description' => fake()->optional()->text,
            'department_id' => \App\Models\Department::factory(),
            'designation_id' => \App\Models\Designation::factory(),
            'branch_id' => \App\Models\Branch::factory(),
            'active' => fake()->randomNumber(1),
        ];
    }
}
