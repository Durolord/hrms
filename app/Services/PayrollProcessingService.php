<?php

namespace App\Services;

use App\Models\Allowance;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollAllowanceSnapshot;
use App\Models\PayrollBonusSnapshot;
use App\Models\PayrollDeductionSnapshot;
use Carbon\Carbon;
use DomainException;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollProcessingService
{
    /**
     * Build (or rebuild) the Pending payroll for an employee and month, snapshotting allowances, deductions and bonuses.
     *
     * @param  string  $month  Y-m
     *
     * @throws DomainException when the payroll is already finalised or the employee had not yet started.
     */
    public function generate(Employee $employee, string $month): Payroll
    {
        $startDate = Carbon::parse($month.'-01 00:00:00');
        $payroll = Payroll::where('employee_id', $employee->id)->where('month', $startDate)->first();
        if ($payroll && $payroll->status !== 'Pending') {
            throw new DomainException("Payroll for {$employee->name} ({$startDate->format('F Y')}) is already {$payroll->status} and cannot be regenerated.");
        }

        $payScale = $employee->designation?->pay_scale;
        $payScaleId = $payScale?->id;
        $basicSalary = $this->proratedBasic($employee, $startDate, (float) ($payScale?->basic_salary ?? 0));

        return DB::transaction(function () use ($employee, $startDate, $payScaleId, $basicSalary) {
            $this->syncStatutoryDeductions($employee, $startDate, $basicSalary);

            $allowances = $payScaleId ? Allowance::where('pay_scale_id', $payScaleId)->get() : collect();
            $deductions = Deduction::where('employee_id', $employee->id)
                ->whereYear('month', $startDate->year)
                ->whereMonth('month', $startDate->month)
                ->get();
            $bonuses = Bonus::where('employee_id', $employee->id)
                ->whereYear('month', $startDate->year)
                ->whereMonth('month', $startDate->month)
                ->get();

            $payroll = Payroll::updateOrCreate(
                ['employee_id' => $employee->id, 'month' => $startDate],
                ['basic_salary' => $basicSalary, 'status' => 'Pending']
            );
            PayrollAllowanceSnapshot::where('payroll_id', $payroll->id)->delete();
            PayrollDeductionSnapshot::where('payroll_id', $payroll->id)->delete();
            PayrollBonusSnapshot::where('payroll_id', $payroll->id)->delete();
            foreach ($allowances as $allowance) {
                PayrollAllowanceSnapshot::create([
                    'payroll_id' => $payroll->id,
                    'allowance_id' => $allowance->id,
                    'pay_scale_id' => $payScaleId,
                    'name' => $allowance->reason,
                    'amount' => $allowance->amount,
                ]);
            }
            foreach ($deductions as $deduction) {
                PayrollDeductionSnapshot::create([
                    'payroll_id' => $payroll->id,
                    'deduction_id' => $deduction->id,
                    'name' => $deduction->reason,
                    'amount' => $deduction->amount,
                ]);
            }
            foreach ($bonuses as $bonus) {
                PayrollBonusSnapshot::create([
                    'payroll_id' => $payroll->id,
                    'bonus_id' => $bonus->id,
                    'name' => $bonus->reason,
                    'amount' => $bonus->amount,
                ]);
            }

            return $payroll;
        });
    }

    /**
     * Process payroll for a specific employee and month and report the outcome as a Filament notification.
     */
    public function processPayrollForEmployee($record, ?array $data): void
    {
        $month = $data['month'] ?? now()->format('Y-m');
        try {
            $this->generate($record, $month);
            Notification::make()
                ->title("Payroll processed successfully for Employee {$record->name}.")
                ->success()
                ->persistent()
                ->send();
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->persistent()->send();
        } catch (Exception $e) {
            Log::error('Payroll Processing Error: '.$e->getMessage(), ['employee_id' => $record->id]);
            Notification::make()
                ->title('Payroll processing error')
                ->body("An error occurred while processing payroll for Employee {$record->name}: ".$e->getMessage())
                ->danger()
                ->persistent()
                ->send();
            throw $e;
        }
    }

    /**
     * Salary for a joiner's first month is paid for the calendar days from the start date.
     */
    private function proratedBasic(Employee $employee, Carbon $month, float $basicSalary): float
    {
        $joined = $employee->employment_start_date ? Carbon::parse($employee->employment_start_date)->startOfDay() : null;
        if (! $joined || $joined->lte($month->copy()->startOfMonth())) {
            return $basicSalary;
        }
        if ($joined->gt($month->copy()->endOfMonth())) {
            throw new DomainException("{$employee->name} had not yet started in {$month->format('F Y')}.");
        }

        return round($basicSalary * ($month->daysInMonth - $joined->day + 1) / $month->daysInMonth, 2);
    }

    /**
     * Keep one Deduction row per configured statutory line so it appears with the other deductions.
     */
    private function syncStatutoryDeductions(Employee $employee, Carbon $month, float $basicSalary): void
    {
        foreach (config('payroll.statutory', []) as $name => $rate) {
            Deduction::where('employee_id', $employee->id)
                ->whereYear('month', $month->year)
                ->whereMonth('month', $month->month)
                ->where('reason', 'like', "{$name} (%")
                ->delete();
            if ($rate > 0) {
                Deduction::create([
                    'employee_id' => $employee->id,
                    'amount' => round($basicSalary * $rate / 100, 2),
                    'month' => $month->copy()->startOfMonth(),
                    'reason' => "{$name} ({$rate}%)",
                    'is_percentage' => false,
                ]);
            }
        }
    }
}
