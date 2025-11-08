<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\PayScaleResource;
use App\Models\PayScale;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class PayScaleLogger extends Logger
{
    public static ?string $model = PayScale::class;

    public static function getLabel(): string|Htmlable|null
    {
        return PayScaleResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name'),
                Field::make('active'),
            ])
            ->relationManagers([
            ]);
    }
}
