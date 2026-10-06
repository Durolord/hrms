<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Payroll;
use Illuminate\Support\Collection;

class HrReports
{
    /**
     * Active headcount per department.
     *
     * @return Collection<int, array{Department: string, Headcount: int}>
     */
    public function headcountByDepartment(): Collection
    {
        return Department::orderBy('name')->get()->map(fn (Department $department) => [
            'Department' => $department->name,
            'Headcount' => Employee::where('department_id', $department->id)->where('active', true)->count(),
        ]);
    }

    /**
     * Approved leave days taken in the year, per leave type, with the average per active employee.
     *
     * @return Collection<int, array{Leave type: string, Days taken: float, Allowance: int, Average per employee: float}>
     */
    public function leaveUsage(int $year): Collection
    {
        $leaves = Leave::where('status', 'Approved')->whereYear('start_date', $year)->get();
        $active = max(Employee::where('active', true)->count(), 1);

        return LeaveType::orderBy('name')->get()->map(function (LeaveType $type) use ($leaves, $active) {
            $days = (float) $leaves->where('leave_type_id', $type->id)->sum(fn (Leave $leave) => $leave->workingDays());

            return [
                'Leave type' => $type->name,
                'Days taken' => $days,
                'Allowance' => (int) $type->max_days,
                'Average per employee' => round($days / $active, 1),
            ];
        });
    }

    /**
     * Net payroll cost per department for a Y-m month, counting Approved and Paid payrolls only.
     *
     * @return Collection<int, array{Department: string, Employees paid: int, Net pay: float}>
     */
    public function payrollCostByDepartment(string $month): Collection
    {
        $date = \Carbon\Carbon::parse($month.'-01');
        $payrolls = Payroll::with('employee', 'current_allowances', 'current_deductions', 'current_bonuses')
            ->whereIn('status', ['Approved', 'Paid'])
            ->whereYear('month', $date->year)
            ->whereMonth('month', $date->month)
            ->get();

        return Department::orderBy('name')->get()->map(function (Department $department) use ($payrolls) {
            $forDepartment = $payrolls->filter(fn (Payroll $p) => $p->employee?->department_id === $department->id);

            return [
                'Department' => $department->name,
                'Employees paid' => $forDepartment->count(),
                'Net pay' => round($forDepartment->sum(fn (Payroll $p) => $p->net_salary), 2),
            ];
        })->filter(fn (array $row) => $row['Employees paid'] > 0)->values();
    }

    /**
     * Render rows as CSV, neutralising spreadsheet formulas.
     */
    public function toCsv(Collection $rows): string
    {
        $out = fopen('php://temp', 'r+');
        if ($rows->isNotEmpty()) {
            fputcsv($out, array_keys($rows->first()));
        }
        foreach ($rows as $row) {
            fputcsv($out, array_map([BankTransferFile::class, 'safeCell'], $row));
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv;
    }
}
