<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PayrollBonusSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PayrollBonusSnapshot>
 */
final class PayrollBonusSnapshotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PayrollBonusSnapshot::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'payroll_id' => \App\Models\Payroll::factory(),
            'bonus_id' => \App\Models\Bonus::factory(),
            'name' => fake()->name,
            'amount' => fake()->word,
        ];
    }
}
