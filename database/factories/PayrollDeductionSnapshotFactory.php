<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PayrollDeductionSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PayrollDeductionSnapshot>
 */
final class PayrollDeductionSnapshotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PayrollDeductionSnapshot::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'payroll_id' => \App\Models\Payroll::factory(),
            'deduction_id' => \App\Models\Deduction::factory(),
            'name' => fake()->name,
            'amount' => fake()->word,
        ];
    }
}
