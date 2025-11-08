<?php

namespace Database\Seeders;

use App\Models\Allowance;
use App\Models\PayScale;
use Illuminate\Database\Seeder;

class AllowanceSeeder extends Seeder
{
    public function run()
    {
        $payScales = PayScale::all();
        foreach ($payScales as $payScale) {
            $allowances = [
                ['reason' => 'Housing Allowance', 'min' => 10000, 'max' => 30000],
                ['reason' => 'Transport Allowance', 'min' => 5000, 'max' => 15000],
                ['reason' => 'Medical Allowance', 'min' => 7000, 'max' => 20000],
                ['reason' => 'Communication Allowance', 'min' => 3000, 'max' => 10000],
            ];
            foreach ($allowances as $allowance) {
                Allowance::create([
                    'pay_scale_id' => $payScale->id,
                    'amount' => rand($allowance['min'], $allowance['max']),
                    'reason' => $allowance['reason'],
                ]);
            }
        }
    }
}
