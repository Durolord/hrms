<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\DesignationResource;
use App\Models\Designation;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class DesignationLogger extends Logger
{
    public static ?string $model = Designation::class;

    public static function getLabel(): string|Htmlable|null
    {
        return DesignationResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name'),
                Field::make('pay_scale.name'),
                Field::make('status'),
            ])
            ->relationManagers([
            ]);
    }
}
