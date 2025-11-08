<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            ['name' => 'Sick Leave', 'description' => 'Leave for sickness or injury.'],
            ['name' => 'Vacation Leave', 'description' => 'Paid time off for vacation.'],
            ['name' => 'Maternity Leave', 'description' => 'Leave for pregnancy and childbirth.'],
            ['name' => 'Paternity Leave', 'description' => 'Leave for fathers during childbirth.'],
            ['name' => 'Unpaid Leave', 'description' => 'Leave without pay.'],
        ];
        foreach ($leaveTypes as $leaveType) {
            LeaveType::create($leaveType);
        }
    }
}
