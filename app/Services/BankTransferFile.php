<?php

namespace App\Services;

use App\Models\Payroll;
use Carbon\Carbon;

class BankTransferFile
{
    /**
     * Net pay for every Approved (not yet paid) payroll in the month, ready for a bulk bank upload.
     *
     * @param  string  $month  Y-m
     * @return array{filename: string, rows: list<list<string|float>>, skipped: list<string>}
     */
    public function build(string $month): array
    {
        $date = Carbon::parse($month.'-01');
        $payrolls = Payroll::with('employee.bank', 'current_allowances', 'current_deductions', 'current_bonuses')
            ->where('status', 'Approved')
            ->whereYear('month', $date->year)
            ->whereMonth('month', $date->month)
            ->get();

        $rows = [['Employee', 'Bank', 'Bank Code', 'Account Number', 'Amount (NGN)', 'Narration']];
        $skipped = [];
        foreach ($payrolls as $payroll) {
            $employee = $payroll->employee;
            if (! $employee->account_number || ! $employee->bank) {
                $skipped[] = $employee->name;

                continue;
            }
            $rows[] = [
                $employee->name,
                $employee->bank->name,
                $employee->bank->code,
                $employee->account_number,
                number_format($payroll->net_salary, 2, '.', ''),
                'Salary '.$date->format('F Y'),
            ];
        }

        return [
            'filename' => 'bank-transfer-'.$date->format('Y-m').'.csv',
            'rows' => $rows,
            'skipped' => $skipped,
        ];
    }

    /**
     * Neutralise spreadsheet formula injection in text cells.
     */
    public static function safeCell(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
