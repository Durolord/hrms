<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run()
    {
        $skills = [
            ['name' => 'PHP', 'description' => 'PHP programming', 'status' => true],
            ['name' => 'JavaScript', 'description' => 'JavaScript programming', 'status' => true],
            ['name' => 'Project Management', 'description' => 'Project management skills', 'status' => true],
            ['name' => 'Communication', 'description' => 'Effective communication skills', 'status' => true],
            ['name' => 'Leadership', 'description' => 'Leadership and team management', 'status' => true],
        ];
        foreach ($skills as $skill) {
            Skill::create($skill);
        }
    }
}
