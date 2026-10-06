<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use App\Services\LeaveRequestValidator;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateLeave extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = LeaveResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        app(LeaveRequestValidator::class)->validate($data);

        return $data;
    }
}
