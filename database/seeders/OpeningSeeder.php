<?php

namespace Database\Seeders;

use App\Models\Opening;
use Illuminate\Database\Seeder;

class OpeningSeeder extends Seeder
{
    public function run()
    {
        // SalesDevelopmentOpeningSeeder adds realistic openings; fall back to factory rows only on an empty table.
        if (Opening::exists()) {
            return;
        }
        Opening::factory()->count(10)->create();
    }
}
