<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class AttendanceLogger extends Logger
{
    public static ?string $model = Attendance::class;

    public static function getLabel(): string|Htmlable|null
    {
        return AttendanceResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('employee.name'),
                Field::make('date'),
                Field::make('time_in'),
                Field::make('time_out'),
                Field::make('break_start'),
                Field::make('break_end'),
            ])
            ->relationManagers([
            ]);
    }
}
