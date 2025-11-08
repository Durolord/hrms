<?php

namespace Database\Seeders;

use App\Models\Deduction;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class DeductionSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::all();
        foreach ($employees as $employee) {
            Deduction::create([
                'employee_id' => $employee->id,
                'amount' => rand(5000, 20000),
                'month' => now()->subMonths(rand(1, 3))->startOfMonth(),
                'reason' => 'Late Attendance Penalty',
            ]);
        }
    }
}
