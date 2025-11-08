<?php

namespace App\Filament\Resources\PayScaleResource\Pages;

use App\Filament\Resources\PayScaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;

class EditPayScale extends EditRecord
{
    use LogEditRecord;

    protected static string $resource = PayScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
