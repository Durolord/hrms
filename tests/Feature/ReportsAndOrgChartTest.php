<?php

use App\Filament\Pages\OrgChart;
use App\Filament\Pages\Reports;
use App\Models\Leave;
use App\Services\HrReports;
use App\Services\PayrollProcessingService;
use Database\Seeders\ExtraPermissionsSeeder;
use Database\Seeders\ShieldSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(ShieldSeeder::class);
    $this->seed(ExtraPermissionsSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    config(['payroll.statutory' => ['Pension' => 0.0, 'Tax (PAYE)' => 0.0]]);
    $this->admin = makeEmployee(['name' => 'Boss', 'email' => 'boss@example.test']);
    $this->admin->user->assignRole('Admin');
    $this->actingAs($this->admin->user);
});

it('reports headcount, leave usage and payroll cost', function () {
    Notification::fake();
    $staff = makeEmployee(basicSalary: 50000);
    $type = makeLeaveType(['max_days' => 10]);
    $leave = Leave::create(['employee_id' => $staff->id, 'leave_type_id' => $type->id, 'start_date' => now()->startOfYear()->addDays(10)->nextWeekday(), 'end_date' => now()->startOfYear()->addDays(10)->nextWeekday(), 'status' => 'Pending']);
    $leave->approve();
    $service = app(PayrollProcessingService::class);
    $service->generate($staff, now()->format('Y-m'))->markApproved();

    $reports = app(HrReports::class);

    expect($reports->headcountByDepartment()->firstWhere('Department', 'Test Department')['Headcount'])->toBe(2)
        ->and($reports->leaveUsage(now()->year)->firstWhere('Leave type', $type->name))->toMatchArray(['Days taken' => 1.0, 'Allowance' => 10])
        ->and($reports->payrollCostByDepartment(now()->format('Y-m'))->first())->toMatchArray(['Employees paid' => 1, 'Net pay' => 50000.0]);
});

it('exports a report as CSV with formula injection neutralised', function () {
    $csv = app(HrReports::class)->toCsv(collect([['Name' => '=cmd|calc', 'Days' => 2.0]]));

    expect($csv)->toContain("Name,Days")->toContain("'=cmd|calc,2")->not->toContain("\n=cmd");
});

it('renders the reports page and downloads a CSV', function () {
    livewire(Reports::class)->assertSuccessful()->assertSee('Headcount by department');
    livewire(Reports::class)->call('export', 'headcount')->assertFileDownloaded('headcount.csv');
});

it('draws the org chart with direct reports and survives a manager cycle', function () {
    $lead = makeEmployee(['name' => 'Team Lead', 'manager_id' => $this->admin->id]);
    makeEmployee(['name' => 'Junior Dev', 'manager_id' => $lead->id]);
    // Corrupt data: two people managing each other must not hang or duplicate.
    $a = makeEmployee(['name' => 'Cycle A']);
    $b = makeEmployee(['name' => 'Cycle B', 'manager_id' => $a->id]);
    $a->update(['manager_id' => $b->id]);

    $tree = livewire(OrgChart::class)->assertSuccessful()->assertSee('Junior Dev')->instance()->tree();

    $boss = $tree->first(fn ($n) => $n['employee']->id === $this->admin->id);
    expect($boss['reports'])->toHaveCount(1)
        ->and($boss['reports'][0]['reports'][0]['employee']->name)->toBe('Junior Dev');
});
