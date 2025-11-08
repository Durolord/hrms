<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Applicant>
 */
final class ApplicantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Applicant::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'phone' => fake()->phoneNumber,
            'email' => fake()->safeEmail,
            'cv' => fake()->word,
            'avatar' => fake()->optional()->word,
            'opening_id' => \App\Models\Opening::factory(),
            'status' => fake()->word,
            'job_status' => fake()->randomElement(['Employed', 'Unemployed']),
        ];
    }
}
