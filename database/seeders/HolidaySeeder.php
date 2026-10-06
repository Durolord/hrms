<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;
        $fixed = [
            '01-01' => "New Year's Day",
            '05-01' => 'Workers\' Day',
            '06-12' => 'Democracy Day',
            '10-01' => 'Independence Day',
            '12-25' => 'Christmas Day',
            '12-26' => 'Boxing Day',
        ];
        foreach ([$year, $year + 1] as $y) {
            foreach ($fixed as $monthDay => $name) {
                Holiday::firstOrCreate(['date' => "{$y}-{$monthDay}"], ['name' => $name]);
            }
        }
    }
}
