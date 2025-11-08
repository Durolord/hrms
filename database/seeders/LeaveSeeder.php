<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Leave;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('en_NG');
        $employeeIds = Employee::pluck('id')->toArray();
        if (empty($employeeIds)) {
            $this->command->error('No employees found. Please seed the employees table first.');

            return;
        }
        $leaves = [];
        for ($i = 0; $i < 20; $i++) {
            $leaves[] = Leave::create([
                'employee_id' => $faker->randomElement($employeeIds),
                'leave_type_id' => $faker->numberBetween(1, 3),
                'start_date' => $faker->dateTimeBetween('-3 months', 'now'),
                'end_date' => $faker->dateTimeBetween('now', '+1 week'),
                'status' => 'Pending',
            ]);
        }
        $randomLeaves = Leave::inRandomOrder()->limit(rand(5, 15))->get();
        foreach ($randomLeaves as $leave) {
            $leave->update(['status' => $faker->randomElement(['Approved', 'Rejected'])]);
        }
    }
}
