<?php

namespace App\Filament\Resources\PayScaleResource\Pages;

use App\Filament\Resources\PayScaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPayScale extends ViewRecord
{
    protected static string $resource = PayScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
