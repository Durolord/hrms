<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    public function getTitle(): string
    {
        $date = request()->query('tableFilters')['date']['day'] ?? now()->toDateString();

        return 'Attendance Overview for '.Carbon::parse($date)->format('M d, Y');
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
