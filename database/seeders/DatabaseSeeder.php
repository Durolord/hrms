<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ShieldSeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            BranchSeeder::class,
            PayScaleSeeder::class,
            DesignationSeeder::class,
            SkillSeeder::class,
            LeaveTypeSeeder::class,
            EmployeeSeeder::class,
            AllowanceSeeder::class,
            DeductionSeeder::class,
            AttendanceSeeder::class,
            BonusSeeder::class,
            LeaveSeeder::class,
            SalesDevelopmentOpeningSeeder::class,
            BankSeeder::class,
        ]);
    }
}
