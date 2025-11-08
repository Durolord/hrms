<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Opening;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ApplicantSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $openings = Opening::all();
        $avatarFileName = '1729505473_nd.jpg';
        $cvFileName = "1729505473_Temitope Ojo's certificate.pdf";
        foreach ($openings as $opening) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                Applicant::create([
                    'opening_id' => $opening->id,
                    'branch_id' => $opening->branch_id,
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'avatar' => 'avatars/'.$avatarFileName,
                    'cv' => 'cvs/'.$cvFileName,
                    'status' => $faker->randomElement(['Applied']),
                ]);
            }
        }
    }
}
