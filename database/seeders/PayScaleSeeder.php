<?php

namespace Database\Seeders;

use App\Models\PayScale;
use Illuminate\Database\Seeder;

class PayScaleSeeder extends Seeder
{
    public function run()
    {
        $payScales = [
            ['name' => 'Grade A', 'basic_salary' => 200000, 'active' => true],
            ['name' => 'Grade B', 'basic_salary' => 150000, 'active' => true],
            ['name' => 'Grade C', 'basic_salary' => 100000, 'active' => true],
            ['name' => 'Grade D', 'basic_salary' => 80000, 'active' => true],
            ['name' => 'Grade E', 'basic_salary' => 50000, 'active' => true],
        ];
        foreach ($payScales as $payScale) {
            PayScale::create($payScale);
        }
    }
}
