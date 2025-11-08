<?php

namespace App\Filament\Resources\OpeningResource\Pages;

use App\Filament\Resources\OpeningResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateOpening extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = OpeningResource::class;
}
