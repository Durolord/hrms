<?php

namespace Database\Seeders;

use App\Filament\Loggers\ApplicantLogger;
use App\Filament\Loggers\LeaveLogger;
use App\Filament\Loggers\PayrollLogger;
use App\Models\Applicant;
use App\Models\Bank;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\PayrollProcessingService;
use App\Support\Demo;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Lived-in sample data around the demo accounts, so every "Try it" step has something to act on:
 * a team with pending leave for the Department Head, payroll history (paid, and drafts awaiting approval),
 * applicants at each stage, bank details for transfer files, and audit-log entries made by the demo users.
 * Runs last; expects the rest of DatabaseSeeder to have run.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Demo::accounts()->keyBy('role')->map(fn (array $account) => User::where('email', $account['email'])->first());
        $head = $accounts->get('Department Head')?->employee;
        $employee = $accounts->get('Employee')?->employee;
        if (! $head || ! $employee) {
            $this->command?->warn('Demo accounts have no employee records; skipping demo data.');

            return;
        }

        // Seeding sends no mail or in-app notifications; the visitor's own actions will.
        Notification::fake();

        $team = $this->buildTeam($head, $employee);
        $this->seedBankDetails();
        $this->seedLeave($team, $employee, $accounts->get('HR Manager'));
        $this->seedPayrolls($accounts->get('Finance Manager'));
        $this->seedRecruitment($accounts->get('HR Manager'));

        Auth::forgetUser();
    }

    /**
     * Act as a demo user, so the audit log (which records the signed-in user) shows them as the author.
     */
    private function actingAs(?User $user): void
    {
        $user ? Auth::setUser($user) : Auth::forgetUser();
    }

    private function seedBankDetails(): void
    {
        $banks = Bank::pluck('id');
        if ($banks->isEmpty()) {
            return;
        }
        Employee::whereNull('bank_id')->get()->each(fn (Employee $employee) => $employee->update([
            'bank_id' => $banks->random(),
            'account_number' => (string) random_int(1000000000, 9999999999),
        ]));
    }

    /**
     * The Department Head leads their department in their branch, with the demo Employee and three colleagues reporting to them.
     *
     * @return Collection<int, Employee>
     */
    private function buildTeam(Employee $head, Employee $employee)
    {
        $head->update(['manager_id' => null]);
        DB::table('department_heads')->updateOrInsert(
            ['department_id' => $head->department_id, 'branch_id' => $head->branch_id],
            ['employee_id' => $head->id, 'created_at' => now(), 'updated_at' => now()],
        );

        $employee->update(['manager_id' => $head->id, 'branch_id' => $head->branch_id, 'department_id' => $head->department_id]);
        $colleagues = Employee::whereHas('user.roles', fn ($query) => $query->where('name', 'Employee'))
            ->whereKeyNot($employee->id)
            ->take(3)
            ->get()
            ->each(fn (Employee $colleague) => $colleague->update([
                'manager_id' => $head->id,
                'branch_id' => $head->branch_id,
                'department_id' => $head->department_id,
            ]));

        return $colleagues->prepend($employee);
    }

    /**
     * Pending requests from the team for the Department Head to decide, plus a used allowance for the demo Employee.
     *
     * @param  Collection<int, Employee>  $team
     */
    private function seedLeave($team, Employee $employee, ?User $hr): void
    {
        $vacation = LeaveType::where('name', 'Vacation Leave')->first() ?? LeaveType::first();
        $sick = LeaveType::where('name', 'Sick Leave')->first() ?? $vacation;
        if (! $vacation) {
            return;
        }
        // Skip the demo Employee: they will file their own request in the walkthrough.
        foreach ($team->skip(1)->values() as $i => $colleague) {
            $start = $this->weekday(now()->addWeeks(2 + $i));
            Leave::create([
                'employee_id' => $colleague->id,
                'leave_type_id' => ($i % 2 ? $sick : $vacation)->id,
                'start_date' => $start,
                'end_date' => $this->weekday($start->copy()->addDays(2)),
                'status' => 'Pending',
            ]);
        }

        $this->actingAs($hr);
        $start = $this->weekday(now()->subMonths(2));
        $leave = Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $vacation->id,
            'start_date' => $start,
            'end_date' => $this->weekday($start->copy()->addDays(3)),
            'status' => 'Pending',
        ]);
        $before = $leave->replicate();
        $leave->approve($hr);
        LeaveLogger::make($before, $leave->fresh())->updated();
    }

    /**
     * Two months back is paid (so every employee has a payslip to download); last month is drafted and awaits Finance.
     */
    private function seedPayrolls(?User $finance): void
    {
        $service = app(PayrollProcessingService::class);
        $paidMonth = now()->subMonths(2)->format('Y-m');
        $draftMonth = now()->subMonth()->format('Y-m');
        $this->actingAs($finance);

        foreach (Employee::where('active', true)->with('designation.pay_scale')->get() as $i => $employee) {
            try {
                $paid = $service->generate($employee, $paidMonth);
                $before = $paid->replicate();
                $paid->update(['status' => 'Approved', 'approved_at' => now()->subMonth()->startOfMonth()->addDays(2)]);
                $paid->update(['status' => 'Paid', 'paid_at' => now()->subMonth()->startOfMonth()->addDays(4)]);
                if ($i < 8) {
                    PayrollLogger::make($before, $paid)->updated();
                }
                $service->generate($employee, $draftMonth);
            } catch (DomainException) {
                // Joined after that month: no payroll is due.
            }
        }
    }

    /**
     * Move some applicants along the pipeline (Applied → Interviewed → Shortlisted) and book interviews.
     */
    private function seedRecruitment(?User $hr): void
    {
        $this->actingAs($hr);
        Applicant::where('status', 'Applied')->inRandomOrder()->take(12)->get()->each(function (Applicant $applicant, int $i) {
            $before = $applicant->replicate();
            $applicant->update(match ($i % 4) {
                0 => ['status' => 'Interviewed'],
                1 => ['status' => 'Shortlisted'],
                2 => ['interview_at' => $this->weekday(now()->addDays(3 + $i))->setTime(10 + $i % 5, 0), 'interview_location' => 'Google Meet'],
                default => ['status' => 'Rejected'],
            });
            ApplicantLogger::make($before, $applicant)->updated();
        });
    }

    private function weekday(Carbon|CarbonInterface $date): Carbon
    {
        $date = Carbon::parse($date)->startOfDay();
        while ($date->isWeekend()) {
            $date->addDay();
        }

        return $date;
    }
}
