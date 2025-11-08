<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class DailyAttendanceOverview extends Page
{
    use InteractsWithRecord;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
