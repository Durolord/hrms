<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BankSeeder extends Seeder
{
    public function run()
    {
        $banks = [
            'Access Bank',
            'Citibank',
            'Ecobank',
            'Fidelity Bank',
            'First Bank of Nigeria',
            'First City Monument Bank',
            'Guaranty Trust Bank',
            'Heritage Bank',
            'Keystone Bank',
            'Polaris Bank',
            'Providus Bank',
            'Stanbic IBTC Bank',
            'Standard Chartered Bank',
            'Sterling Bank',
            'SunTrust Bank',
            'Union Bank of Nigeria',
            'United Bank for Africa',
            'Unity Bank',
            'Wema Bank',
            'Zenith Bank',
            'Jaiz Bank',
            'Globus Bank',
            'Titan Trust Bank',
            'Parallex Bank',
        ];
        foreach ($banks as $name) {
            $code = Str::slug($name);
            Bank::updateOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }
    }
}
