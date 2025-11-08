<?php

namespace App\Filament\Resources\ApplicantResource\Pages;

use App\Filament\Resources\ApplicantResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateApplicant extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = ApplicantResource::class;
}
