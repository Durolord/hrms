<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\DeductionResource;
use App\Models\Deduction;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class DeductionLogger extends Logger
{
    public static ?string $model = Deduction::class;

    public static function getLabel(): string|Htmlable|null
    {
        return DeductionResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('employee.name'),
                Field::make('amount'),
                Field::make('month'),
                Field::make('reason'),
                Field::make('is_percentage')
                    ->boolean(),
            ])
            ->relationManagers([
            ]);
    }
}
