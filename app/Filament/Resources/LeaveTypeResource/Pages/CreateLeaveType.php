<?php

namespace App\Filament\Resources\LeaveTypeResource\Pages;

use App\Filament\Resources\LeaveTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateLeaveType extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = LeaveTypeResource::class;
}
