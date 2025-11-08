<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateEmployee extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = EmployeeResource::class;
}
