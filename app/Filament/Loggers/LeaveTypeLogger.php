<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\LeaveTypeResource;
use App\Models\LeaveType;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class LeaveTypeLogger extends Logger
{
    public static ?string $model = LeaveType::class;

    public static function getLabel(): string|Htmlable|null
    {
        return LeaveTypeResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name'),
                Field::make('description'),
                Field::make('deduction_amount'),
                Field::make('is_percentage'),
                Field::make('max_days'),
            ])
            ->relationManagers([
            ]);
    }
}
