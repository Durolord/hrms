<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\PerformanceReview;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class PerformanceReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $employees = Employee::with('department')->get();
        $faker = Faker::create('en_NG');
        foreach ($employees as $employee) {
            for ($i = 0; $i < 12; $i++) {
                PerformanceReview::create([
                    'employee_id' => $employee->id,
                    'month' => now()->subMonths($i)->startOfMonth(),
                    'reviewer_id' => $employee->manager_id ?? 9,
                    'rating' => rand(1, 5),
                    'remarks' => 'Performance review for '.$faker->paragraph,
                ]);
            }
        }
    }
}
