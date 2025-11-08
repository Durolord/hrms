<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $employeeQuery = Employee::query();
        if (! $user->can('view_outside_branch_employee')) {
            $employeeQuery->where('branch_id', $user->employee->branch->id);
        }

        return [
            Stat::make('Total Employees', $employeeQuery->count())
                ->description('Total number of employees')
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make('New Hires (Last 6 Months)', $employeeQuery->where('created_at', '>=', now()->subMonths(6))->count())
                ->description('Employees hired in the last 6 months')
                ->icon('heroicon-o-chart-bar')
                ->color('success'),
        ];
    }
}
