<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Attendance>
 */
final class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'time_in' => fake()->dateTime(),
            'time_out' => fake()->optional()->dateTime(),
            'break_start' => fake()->optional()->dateTime(),
            'break_end' => fake()->optional()->dateTime(),
            'employee_id' => \App\Models\Employee::factory(),
        ];
    }
}
