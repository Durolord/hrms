<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\PayrollResource;
use App\Models\Payroll;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class PayrollLogger extends Logger
{
    public static ?string $model = Payroll::class;

    public static function getLabel(): string|Htmlable|null
    {
        return PayrollResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('employee.name'),
                Field::make('month')
                    ->date('F Y'),
                Field::make('basic_salary'),
                Field::make('status'),
            ])
            ->relationManagers([
            ]);
    }
}
