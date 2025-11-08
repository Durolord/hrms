<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\AllowanceResource;
use App\Models\Allowance;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class AllowanceLogger extends Logger
{
    public static ?string $model = Allowance::class;

    public static function getLabel(): string|Htmlable|null
    {
        return AllowanceResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('pay_scale.name'),
                Field::make('employee.name'),
                Field::make('amount'),
                Field::make('reason'),
                Field::make('is_percentage'),
            ])
            ->relationManagers([
            ]);
    }
}
