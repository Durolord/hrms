<?php

namespace App\Jobs;

use App\Models\Allowance;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\Payroll;
use App\Models\PayrollAllowanceSnapshot;
use App\Models\PayrollBonusSnapshot;
use App\Models\PayrollDeductionSnapshot;
use Bytexr\QueueableBulkActions\Filament\Actions\ActionResponse;
use Bytexr\QueueableBulkActions\Jobs\BulkActionJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class Payrolls extends BulkActionJob
{
    protected function action($record, ?array $data): ActionResponse
    {
        try {
            $month = $data['month'] ?? now()->format('Y-m');
            $startDate = Carbon::parse($month.'-01 00:00:00');
            $payroll = Payroll::where('employee_id', $record->id)
                ->where('month', $startDate)
                ->first();
            if ($payroll && $payroll->status !== 'Pending') {
                $formattedMonth = Carbon::parse($month)->format('F Y');

                return ActionResponse::make()
                    ->failure()
                    ->message("Payroll for {$record->name} ({$formattedMonth}) is already {$payroll->status} and cannot be regenerated.");
            }
            $payScaleId = optional($record->designation)->pay_scale->id ?? null;
            $basicSalary = optional(optional($record->designation)->pay_scale)->basic_salary ?? 0;
            $allowances = $payScaleId ? Allowance::where('pay_scale_id', $payScaleId)->get() : collect();
            $deductions = Deduction::where('employee_id', $record->id)
                ->whereYear('month', $startDate->year)
                ->whereMonth('month', $startDate->month)
                ->get();
            $bonuses = Bonus::where('employee_id', $record->id)
                ->whereYear('month', $startDate->year)
                ->whereMonth('month', $startDate->month)
                ->get();
            DB::transaction(function () use ($record, $startDate, $payScaleId, $allowances, $deductions, $bonuses, $basicSalary, &$payroll) {
                $payroll = Payroll::updateOrCreate(
                    [
                        'employee_id' => $record->id,
                        'month' => $startDate,
                    ],
                    [
                        'basic_salary' => $basicSalary,
                        'status' => 'Pending',
                    ]
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
            });

            return ActionResponse::make()
                ->success()
                ->message("Payroll processed successfully for Employee {$record->name}.");
        } catch (Throwable $e) {
            Log::error(
                "Error processing payroll for Employee {$record->name}: {$e->getMessage()} 
                | Code: {$e->getCode()} 
                | File: {$e->getFile()} 
                | Line: {$e->getLine()}"
            );

            return ActionResponse::make()
                ->failure()
                ->message(
                    "Error processing payroll for Employee {$record->name}: {$e->getMessage()} 
                | Code: {$e->getCode()} 
                | File: {$e->getFile()} 
                | Line: {$e->getLine()}"
                );
        }
    }
}
