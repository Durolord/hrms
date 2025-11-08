<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AttendanceSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AttendanceSummary>
 */
final class AttendanceSummaryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttendanceSummary::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'total_attendances' => fake()->randomNumber(),
        ];
    }
}
