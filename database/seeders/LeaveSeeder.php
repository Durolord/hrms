<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LeaveSeeder extends Seeder
{
    public function run()
    {
        $employeeIds = Employee::pluck('id')->toArray();
        if (empty($employeeIds)) {
            $this->command->error('No employees found. Please seed the employees table first.');

            return;
        }
        $leaves = [];
        $leaveTypeIds = [1, 2, 3];
        $baseDate = Carbon::now()->subDays(45);
        for ($i = 0; $i < 20; $i++) {
            $startDate = $baseDate->copy()->addDays($i * 2);
            $endDate = $startDate->copy()->addDays(3 + ($i % 5));
            $leaves[] = Leave::create([
                'employee_id' => $employeeIds[$i % count($employeeIds)],
                'leave_type_id' => $leaveTypeIds[$i % count($leaveTypeIds)],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'Pending',
            ]);
        }
        $maxCount = min(15, count($leaves));
        $takeCount = min($maxCount, random_int(5, $maxCount));
        $randomLeaves = collect($leaves)->shuffle()->take($takeCount);
        foreach ($randomLeaves as $leave) {
            $leave->update(['status' => random_int(0, 1) === 0 ? 'Approved' : 'Rejected']);
        }
    }
}
