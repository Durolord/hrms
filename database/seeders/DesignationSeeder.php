<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\PayScale;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run()
    {
        $payScales = PayScale::all();
        foreach ($payScales as $payScale) {
            Designation::create([
                'name' => "{$payScale->name} Designation",
                'pay_scale_id' => $payScale->id,
                'status' => true,
            ]);
        }
    }
}
