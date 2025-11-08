<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\BonusResource;
use App\Models\Bonus;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class BonusLogger extends Logger
{
    public static ?string $model = Bonus::class;

    public static function getLabel(): string|Htmlable|null
    {
        return BonusResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('employee_id'),
                Field::make('amount'),
                Field::make('month'),
                Field::make('reason'),
                Field::make('is_percentage'),
            ])
            ->relationManagers([
            ]);
    }
}
