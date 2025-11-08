<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceSummary;
use Illuminate\Console\Command;

class GenerateAttendanceSummary extends Command
{
    protected $signature = 'attendance:summary {--all : Generate for all days} {--date= : Generate for a specific date (YYYY-MM-DD)}';

    protected $description = 'Generate an attendance summary for today, a specific date, or all dates';

    public function handle()
    {
        if ($this->option('all')) {
            $this->generateForAllDays();
        } elseif ($this->option('date')) {
            $this->generateForDate($this->option('date'));
        } else {
            $this->generateForDate(now()->toDateString());
        }
        $this->info('Attendance summary generated successfully!');
    }

    private function generateForAllDays()
    {
        $attendanceCounts = Attendance::selectRaw('date, COUNT(*) as total_attendances')
            ->groupBy('date')
            ->get();
        foreach ($attendanceCounts as $record) {
            $this->generateForDate($record->date, $record->total_attendances);
        }
    }

    private function generateForDate(string $date, ?int $count = null)
    {
        if ($count === null) {
            $count = Attendance::whereDate('date', $date)->count();
        }
        AttendanceSummary::updateOrCreate(
            ['date' => $date],
            ['total_attendances' => $count]
        );
    }
}
