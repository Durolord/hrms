<?php

namespace App\Filament\Widgets;

use App\Models\Payroll;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\Auth;

class PayrollSummaryChartWidget extends LineChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Payroll Financials Over Time';

    protected function getData(): array
    {
        $user = Auth::user();
        $branchId = $user->employee->branch_id ?? null;
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $query = Payroll::with([
            'current_allowances',
            'current_bonuses',
            'current_deductions',
        ])
            ->where('status', 'Paid')
            ->whereBetween('month', [$startDate, $endDate]);
        if (! $user->can('view_outside_branch_employee')) {
            $query->whereHas('employee', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $payrolls = $query->get();
        $labels = [];
        $dataPayroll = [];
        $dataBonus = [];
        $dataDeductions = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $monthLabel = $current->format('Y-m');
            $labels[] = $monthLabel;
            $monthlyPayrolls = $payrolls->filter(function ($payroll) use ($monthLabel) {
                return $payroll->month->format('Y-m') === $monthLabel;
            });
            $totalBasicSalary = $monthlyPayrolls->sum('basic_salary');
            $totalAllowances = $monthlyPayrolls->sum(function ($payroll) {
                return $payroll->current_allowances->sum('amount');
            });
            $totalBonus = $monthlyPayrolls->sum(function ($payroll) {
                return $payroll->current_bonuses->sum('amount');
            });
            $totalDeductions = $monthlyPayrolls->sum(function ($payroll) {
                return $payroll->current_deductions->sum('amount');
            });
            $dataPayroll[] = (float) ($totalBasicSalary + $totalAllowances);
            $dataBonus[] = (float) $totalBonus;
            $dataDeductions[] = (float) (-1 * $totalDeductions);
            $current->addMonth();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Basic Salary + Allowances',
                    'data' => $dataPayroll,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Bonus Distributions',
                    'data' => $dataBonus,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Deductions',
                    'data' => $dataDeductions,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'fill' => true,
                ],
            ],
        ];
    }
}
