<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class DepartmentLogger extends Logger
{
    public static ?string $model = Department::class;

    public static function getLabel(): string|Htmlable|null
    {
        return DepartmentResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name'),
                Field::make('status'),
            ])
            ->relationManagers([
            ]);
    }
}
