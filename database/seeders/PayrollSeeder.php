<?php

namespace Database\Seeders;

use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run()
    {
        $employees = Employee::all();
        foreach ($employees as $employee) {
            $payroll = Payroll::create([
                'employee_id' => $employee->id,
                'month' => now()->subMonth(),
                'basic_salary' => $employee->designation->pay_scale->basic_salary ?? 50000,
                'status' => 'Pending',
            ]);
            $allowances = Allowance::where('pay_scale_id', $employee->designation->pay_scale_id)
                ->get();
            $deductions = Deduction::where('employee_id', $employee->id)->get();
            foreach ($deductions as $deduction) {
                $payroll->deductions()->attach($deduction->id, ['amount' => $deduction->amount]);
            }
            $payroll->save();
        }
    }
}
