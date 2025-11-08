<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Opening;

class OpeningSeeder extends Seeder
{
    public function run()
    {
        // Create a handful of openings using the factory
        Opening::factory()->count(10)->create();
    }
}
