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

class EmployeeStats extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'Employee Stats';

    protected function getCards(): array
    {
        $user = Auth::user();
        $cards = [];
        $cards[] = Card::make('Total Employees', Employee::count())
            ->icon('heroicon-o-user-group');
        $cards[] = Card::make('Open Positions', Opening::where('active', true)->count())
            ->icon('heroicon-o-briefcase');
        $departments = Department::count();
        $branches = Branch::count();
        $cards[] = Card::make('Departments/Branches', "{$departments} / {$branches}")
            ->icon('heroicon-o-office-building');
        $cards[] = Card::make('Recent Activity', 0)
            ->icon('heroicon-o-clock')
            ->description('Recent change log events');

        return $cards;
    }
}
