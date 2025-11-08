<?php

namespace App\Filament\Resources\AllowanceResource\Pages;

use App\Filament\Resources\AllowanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;

class EditAllowance extends EditRecord
{
    use LogEditRecord;

    protected static string $resource = AllowanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
