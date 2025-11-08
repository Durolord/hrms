<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\LeaveResource;
use App\Models\Leave;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class LeaveLogger extends Logger
{
    public static ?string $model = Leave::class;

    public static function getLabel(): string|Htmlable|null
    {
        return LeaveResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('start_date')
                    ->date(),
                Field::make('end_date')
                    ->date(),
                Field::make('approved_on'),
                Field::make('status'),
                Field::make('deducted_from_payroll'),
                Field::make('employee.name'),
                Field::make('leave_type.name'),
                Field::make('approver.name'),
            ])
            ->relationManagers([
            ]);
    }
}
