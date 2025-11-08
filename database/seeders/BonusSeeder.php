<?php

namespace Database\Seeders;

use App\Models\Bonus;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class BonusSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::all();
        foreach ($employees as $employee) {
            for ($i = 0; $i < 4; $i++) {
                Bonus::create([
                    'employee_id' => $employee->id,
                    'amount' => rand(5000, 30000),
                    'month' => now()->subMonths(rand(1, 3))->startOfMonth(),
                    'reason' => 'Quarterly Performance Bonus',
                ]);
            }
        }
    }
}
