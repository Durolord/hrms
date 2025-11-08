<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateAttendance extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = AttendanceResource::class;
}
