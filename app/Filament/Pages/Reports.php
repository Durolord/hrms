<?php

namespace App\Filament\Pages;

use App\Services\HrReports;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Payroll and Compensation';

    protected static string $view = 'filament.pages.reports';

    public int $year;

    public string $month;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('view_any_payroll');
    }

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->format('Y-m');
    }

    /** @return array<string, Collection> */
    public function reports(): array
    {
        $reports = app(HrReports::class);

        return [
            'headcount' => $reports->headcountByDepartment(),
            'leave' => $reports->leaveUsage($this->year),
            'payroll' => $reports->payrollCostByDepartment($this->month),
        ];
    }

    public function monthOptions(): array
    {
        return collect(range(0, 11))
            ->mapWithKeys(fn ($i) => [now()->subMonths($i)->format('Y-m') => now()->subMonths($i)->format('F Y')])
            ->all();
    }

    public function export(string $report)
    {
        $rows = $this->reports()[$report] ?? abort(404);
        $name = match ($report) {
            'leave' => "leave-usage-{$this->year}",
            'payroll' => "payroll-cost-{$this->month}",
            default => 'headcount',
        };
        $csv = app(HrReports::class)->toCsv($rows);

        return response()->streamDownload(fn () => print ($csv), "{$name}.csv", ['Content-Type' => 'text/csv']);
    }
}
