<?php

namespace App\Filament\Resources\DesignationResource\Pages;

use App\Filament\Resources\DesignationResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateDesignation extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = DesignationResource::class;
}
