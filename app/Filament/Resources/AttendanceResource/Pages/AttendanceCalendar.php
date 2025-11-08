<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use Filament\Resources\Pages\Page;

class AttendanceCalendar extends Page
{
    protected static string $resource = AttendanceResource::class;

    protected static string $view = 'filament.resources.attendance-resource.pages.attendance-calendar';

    public ?string $selectedDate = null;

    public array $dayStats = [];

    /**
     * Return header widgets.
     *
     * This registers our custom FullCalendar widget so that it appears on the page.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceResource\Widgets\AttendanceCalendarWidget::class,
        ];
    }

    /**
     * Called via Livewire when a day/event is clicked in the calendar.
     *
     * @param  string  $date  in "YYYY-MM-DD" format.
     */
    public function openDayModal(string $date): void
    {
        $this->selectedDate = $date;
        $this->calculateDayStats($date);
        $this->dispatchBrowserEvent('open-attendance-modal');
    }

    /**
     * Calculate overall statistics for a given day.
     */
    protected function calculateDayStats(string $date): void
    {
        $attendances = Attendance::whereDate('created_at', $date)->get();
        $this->dayStats = [
            'total' => $attendances->count(),
        ];
    }
}
