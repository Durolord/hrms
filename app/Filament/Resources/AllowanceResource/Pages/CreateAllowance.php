<?php

namespace App\Filament\Resources\AllowanceResource\Pages;

use App\Filament\Resources\AllowanceResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateAllowance extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = AllowanceResource::class;
}
