<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Artisan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::all();
        $startDate = now()->subMonth()->startOfMonth();
        $endDate = now()->subMonth()->endOfMonth();
        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();

                    continue;
                }
                if (rand(1, 100) <= 10) {
                    $currentDate->addDay();

                    continue;
                }
                $timeIn = Carbon::parse($currentDate->toDateString().' 08:00:00')
                    ->addMinutes(rand(0, 30));
                $breakStart = $timeIn->copy()->addHours(4)->addMinutes(rand(0, 10));
                $breakEnd = $breakStart->copy()->addMinutes(rand(30, 60));
                $timeOut = Carbon::parse($currentDate->toDateString().' 17:00:00')
                    ->subMinutes(rand(0, 30));
                if (! Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', $currentDate)
                    ->exists()) {
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $currentDate,
                        'time_in' => $timeIn,
                        'break_start' => $breakStart,
                        'break_end' => $breakEnd,
                        'time_out' => $timeOut,
                    ]);
                }
                $currentDate->addDay();
            }
        }
        Artisan::call('attendance:summary', ['--all' => true]);
        $this->command->info('Attendance summaries for all days have been generated.');
    }
}
