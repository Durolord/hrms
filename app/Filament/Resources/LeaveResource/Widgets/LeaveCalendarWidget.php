<?php

namespace App\Filament\Resources\LeaveResource\Widgets;

use App\Models\Leave;
use Carbon\Carbon;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class LeaveCalendarWidget extends FullCalendarWidget
{
    public function fetchEvents(array $fetchInfo): array
    {
        $query = Leave::query();
        $user = auth()->user();
        $employee = $user->employee;
        if (! $user->can('view_non_managed_leave')) {
            if ($user->can('view_outside_branch_employee')) {
                $query->join('employees', 'leaves.employee_id', '=', 'employees.id')
                    ->where('employees.manager_id', $employee->id);
            } else {
                $query->join('employees', 'leaves.employee_id', '=', 'employees.id')
                    ->where('employees.branch_id', $employee->branch->id)
                    ->where('employees.manager_id', $employee->id);
            }
        } elseif (! $user->can('view_outside_branch_employee')) {
            $query->join('employees', 'leaves.employee_id', '=', 'employees.id')
                ->where('employees.branch_id', $employee->branch->id);
        }

        return $query
            ->whereIn('status', ['Pending', 'Approved'])
            ->whereDate('start_date', '<=', $fetchInfo['end'])
            ->whereDate('end_date', '>=', $fetchInfo['start'])
            ->get()
            ->map(fn (Leave $leave) => EventData::make()
                ->id($leave->id)
                ->title($leave->employee->name.' ('.$leave->status.')')
                ->start(Carbon::parse($leave->start_date))
                ->allDay(true)
                ->end(Carbon::parse($leave->end_date)->addDay())
                ->backgroundColor($leave->status === 'Approved' ? '#D4EDDA' : '#FFF3CD')
                ->borderColor($leave->status === 'Approved' ? '#28A745' : '#FFC107')
                ->textColor($leave->status === 'Approved' ? '#155724' : '#856404')
                ->url(route('filament.admin.resources.leaves.view', $leave->id))
            )
            ->toArray();
    }
}
