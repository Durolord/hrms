<?php

namespace App\Filament\Pages\Help;

class LeaveAndAttendance extends HelpPage
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Leave & attendance';

    protected static ?string $title = 'Leave & attendance';

    protected static ?string $slug = 'help/leave-and-attendance';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.help.leave-and-attendance';
}
