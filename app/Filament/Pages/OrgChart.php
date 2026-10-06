<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class OrgChart extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?string $title = 'Organisation chart';

    protected static string $view = 'filament.pages.org-chart';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('view_any_employee');
    }

    /**
     * Reporting tree: people with no manager (or whose manager is inactive or deleted) are roots.
     * A manager cycle cannot hang the page: every person is placed at most once.
     *
     * @return Collection<int, array{employee: Employee, reports: Collection}>
     */
    public function tree(): Collection
    {
        $employees = Employee::where('active', true)->with('designation', 'department')->orderBy('name')->get();
        $byManager = $employees->groupBy('manager_id');
        $ids = $employees->pluck('id')->flip();
        $placed = [];

        $build = function (Employee $employee) use (&$build, $byManager, &$placed) {
            $placed[$employee->id] = true;

            return [
                'employee' => $employee,
                'reports' => ($byManager[$employee->id] ?? collect())
                    ->reject(fn (Employee $report) => isset($placed[$report->id]))
                    ->map(fn (Employee $report) => $build($report))
                    ->values(),
            ];
        };

        $roots = $employees->filter(fn (Employee $e) => $e->manager_id === null || ! isset($ids[$e->manager_id]));

        return $roots->map(fn (Employee $root) => $build($root))->values();
    }
}
