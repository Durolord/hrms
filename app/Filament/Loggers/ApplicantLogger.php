<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\ApplicantResource;
use App\Models\Applicant;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class ApplicantLogger extends Logger
{
    public static ?string $model = Applicant::class;

    public static function getLabel(): string|Htmlable|null
    {
        return ApplicantResource::getModelLabel();
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name'),
                Field::make('phone'),
                Field::make('email'),
                Field::make('opening.title'),
                Field::make('cv'),
                Field::make('avatar'),
                Field::make('status'),
                Field::make('job_status'),
            ])
            ->relationManagers([
            ]);
    }
}
