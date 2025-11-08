<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Facades\Auth;

class EmployeeDistributionWidget extends BarChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Employee Distribution by Department';

    protected function getData(): array
    {
        $user = Auth::user();
        $query = Employee::query()->with('department');
        if (! $user->can('view_outside_branch_employee')) {
            $branchId = $user->employee->branch_id;
            $query->where('branch_id', $branchId);
        }
        $employees = $query->get();
        $grouped = $employees->groupBy(function ($employee) {
            return $employee->department->name ?? 'N/A';
        });
        $labels = [];
        $data = [];
        foreach ($grouped as $departmentName => $group) {
            $labels[] = $departmentName;
            $data[] = $group->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.7)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}
