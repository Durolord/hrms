<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Performance;
use Illuminate\Database\Seeder;

class PerformanceSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::all();
        foreach ($employees as $employee) {
            for ($i = 0; $i < 12; $i++) {
                Performance::create([
                    'employee_id' => $employee->id,
                    'month' => now()->subMonths($i)->startOfMonth(),
                    'rating' => rand(3, 10),
                    'remarks' => 'Performance review for '.now()->subMonths($i)->format('F Y'),
                ]);
            }
        }
    }
}
