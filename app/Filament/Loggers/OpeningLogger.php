<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\OpeningResource;
use App\Models\Opening;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class OpeningLogger extends Logger
{
    public static ?string $model = Opening::class;

    public static function getLabel(): string|Htmlable|null
    {
        return OpeningResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('title'),
                Field::make('description'),
                Field::make('department.name'),
                Field::make('designation.name'),
                Field::make('branch.name'),
                Field::make('active'),
            ])
            ->relationManagers([
            ]);
    }
}
