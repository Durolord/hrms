<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            ['name' => 'Human Resources', 'status' => true],
            ['name' => 'Finance', 'status' => true],
            ['name' => 'IT', 'status' => true],
            ['name' => 'Sales', 'status' => true],
            ['name' => 'Operations', 'status' => true],
        ];
        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
