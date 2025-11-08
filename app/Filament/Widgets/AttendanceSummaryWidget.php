<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AttendanceSummaryWidget extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Attendance Summary';

    protected function getStats(): array
    {
        $user = Auth::user();
        $dailyQuery = Attendance::query()->whereDate('date', Carbon::today());
        if (! $user->can('view_outside_branch_employee')) {
            $dailyQuery->whereHas('employee', function ($q) use ($user) {
                $q->where('branch_id', $user->employee->branch_id);
            });
        }
        $dailyAttendance = $dailyQuery->count();
        $startOfWeek = Carbon::today()->subDays(6);
        $weeklyQuery = Attendance::query()->whereBetween('date', [$startOfWeek, Carbon::today()]);
        if (! $user->can('view_outside_branch_employee')) {
            $weeklyQuery->whereHas('employee', function ($q) use ($user) {
                $q->where('branch_id', $user->employee->branch_id);
            });
        }
        $weeklyAttendance = $weeklyQuery->count();

        return [
            Stat::make('Today', number_format($dailyAttendance))
                ->description('Attendances recorded today')
                ->icon('heroicon-o-calendar')
                ->color('success'),
            Stat::make('This Week', number_format($weeklyAttendance))
                ->description('Attendances recorded in the last 7 days')
                ->icon('heroicon-o-clock')
                ->color('primary'),
        ];
    }
}
