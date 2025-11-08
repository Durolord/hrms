<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Opening;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class OrganizationOverview extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Organization Overview';

    protected function getCards(): array
    {
        $user = Auth::user();
        $cards = [];
        $employeeCount = $user->can('view_outside_branch_employee')
            ? Employee::count()
            : Employee::where('branch_id', $user->employee->branch_id)->count();
        $cards[] = Card::make('Total Employees', $employeeCount)
            ->icon('heroicon-o-user-group');
        $openPositions = $user->can('view_outside_branch_employee')
            ? Opening::where('active', true)->count()
            : Opening::where('active', true)
                ->where('branch_id', $user->employee->branch_id)
                ->count();
        $cards[] = Card::make('Open Positions', $openPositions)
            ->icon('heroicon-o-briefcase');
        $departments = Department::count();
        $branches = Branch::count();
        $cards[] = Card::make('Departments/Branches', "{$departments} / {$branches}")
            ->icon('heroicon-o-building-office');
        $recentActivityCount = Activity::where('created_at', '>=', now()->subWeek())->count();
        $cards[] = Card::make('Recent Activity', $recentActivityCount)
            ->icon('heroicon-o-clock')
            ->description('Activities in the last 7 days');

        return $cards;
    }
}
