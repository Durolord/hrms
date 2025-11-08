<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AttendanceCalendarWidget extends FullCalendarWidget
{
    /**
     * Load events from the Attendance model.
     * This groups attendance by date and filters by branch if necessary.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $user = Auth::user();
        $query = Attendance::query()->whereBetween('date', [$fetchInfo['start'], $fetchInfo['end']]);
        if (! $user->can('view_outside_branch_employee')) {
            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('branch_id', $user->employee->branch_id);
            });
        }

        return $query->get()
            ->groupBy('date')
            ->map(fn ($attendances, $date) => EventData::make()
                ->id($date)
                ->title('Attendance: '.$attendances->count())
                ->start(Carbon::parse($date))
                ->allDay(true)
                ->url(
                    url: AttendanceResource::getUrl('overview').'?'.http_build_query([
                        'tableFilters' => [
                            'date' => [
                                'day' => Carbon::parse($date)->format('Y-m-d'),
                            ],
                        ],
                    ]),
                    shouldOpenUrlInNewTab: true
                )
            )
            ->values()
            ->toArray();
    }
}
