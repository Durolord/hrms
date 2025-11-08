<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            ['name' => 'Lagos Branch', 'address' => '123 Lagos Street, Lagos', 'phone' => '08012345678', 'status' => true],
            ['name' => 'Abuja Branch', 'address' => '456 Abuja Street, Abuja', 'phone' => '08087654321', 'status' => true],
        ];
        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}
