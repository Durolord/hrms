<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Applicant;
use App\Models\Opening;
use Faker\Factory as Faker;

class ApplicantSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Ensure there are openings to attach applicants to
        $openingIds = Opening::pluck('id')->toArray();

        if (empty($openingIds)) {
            Opening::factory()->count(5)->create();
            $openingIds = Opening::pluck('id')->toArray();
        }

        $openings = Opening::all();

        // For each opening create a random number of applicants
        foreach ($openings as $opening) {
            $count = rand(5, 15);

            for ($i = 0; $i < $count; $i++) {
                Applicant::create([
                    'opening_id' => $opening->id,
                    // 'branch_id' => $opening->branch_id ?? null,
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'avatar' => null,
                    'cv' => null,
                    'status' => 'Applied',
                    'job_status' => $faker->randomElement(['Employed', 'Unemployed']),
                ]);
            }
        }
    }
}
