<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LeaveRequestValidator
{
    /**
     * Validate a new leave request; throws a ValidationException keyed under $prefix (the Livewire form path).
     *
     * @param  array{employee_id:int, leave_type_id:int, start_date:mixed, end_date:mixed, is_half_day?:bool}  $data
     */
    public function validate(array $data, string $prefix = 'data.'): float
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();
        $half = (bool) ($data['is_half_day'] ?? false);
        $fail = fn (string $field, string $message) => throw ValidationException::withMessages([$prefix.$field => $message]);

        if ($end->lt($start)) {
            $fail('end_date', 'The end date cannot be before the start date.');
        }
        if ($half && ! $start->equalTo($end)) {
            $fail('is_half_day', 'A half day can only be requested for a single date.');
        }

        $days = Leave::countWorkingDays($start, $end, $half);
        if ($days <= 0) {
            $fail('start_date', 'The selected dates contain no working days (weekends and public holidays are excluded).');
        }

        $employee = Employee::findOrFail($data['employee_id']);
        $overlap = $employee->leaves()
            ->whereIn('status', ['Approved', 'Pending'])
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->exists();
        if ($overlap) {
            $fail('start_date', 'You already have an approved or pending leave request overlapping these dates.');
        }

        $leaveType = LeaveType::find($data['leave_type_id']);
        $balance = $employee->leaveBalanceFor($leaveType, $start->year);
        if ($leaveType && $days > $balance) {
            $fail('leave_type_id', "Only {$balance} day(s) of {$leaveType->name} remain; this request needs {$days}.");
        }

        return $days;
    }
}
