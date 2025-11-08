<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PayrollAllowanceSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PayrollAllowanceSnapshot>
 */
final class PayrollAllowanceSnapshotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PayrollAllowanceSnapshot::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'payroll_id' => \App\Models\Payroll::factory(),
            'allowance_id' => fake()->randomNumber(),
            'pay_scale_id' => fake()->randomNumber(),
            'name' => fake()->optional()->name,
            'amount' => fake()->word,
        ];
    }
}
