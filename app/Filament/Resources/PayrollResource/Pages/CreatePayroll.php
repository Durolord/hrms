<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreatePayroll extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = PayrollResource::class;
}
