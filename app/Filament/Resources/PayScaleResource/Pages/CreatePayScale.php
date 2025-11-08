<?php

namespace App\Filament\Resources\PayScaleResource\Pages;

use App\Filament\Resources\PayScaleResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreatePayScale extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = PayScaleResource::class;
}
