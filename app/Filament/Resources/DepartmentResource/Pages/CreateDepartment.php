<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateDepartment extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = DepartmentResource::class;
}
