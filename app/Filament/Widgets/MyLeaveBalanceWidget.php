<?php

namespace App\Filament\Widgets;

use App\Models\LeaveType;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyLeaveBalanceWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'My leave balance';

    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->employee;
    }

    protected function getStats(): array
    {
        $employee = auth()->user()->employee;
        $year = now()->year;

        return LeaveType::orderBy('name')->get()
            ->map(function (LeaveType $type) use ($employee, $year) {
                $remaining = $employee->leaveBalanceFor($type, $year);
                $pending = $employee->totalLeaveDaysTaken($type->id, $year, ['Pending']);

                return Stat::make($type->name, rtrim(rtrim(number_format($remaining, 1), '0'), '.').' of '.$type->max_days.' days left')
                    ->description($pending > 0 ? "{$pending} day(s) pending approval" : "{$year} allowance")
                    ->color($remaining <= 0 ? 'danger' : 'success');
            })
            ->all();
    }
}
