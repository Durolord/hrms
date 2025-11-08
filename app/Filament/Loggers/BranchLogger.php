<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class BranchLogger extends Logger
{
    public static ?string $model = Branch::class;

    public static function getLabel(): string|Htmlable|null
    {
        return BranchResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name'),
                Field::make('address'),
                Field::make('phone'),
                Field::make('status'),
            ])
            ->relationManagers([
            ]);
    }
}
