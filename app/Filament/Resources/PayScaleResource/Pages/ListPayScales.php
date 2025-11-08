<?php

namespace App\Filament\Resources\PayScaleResource\Pages;

use App\Filament\Resources\PayScaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayScales extends ListRecords
{
    protected static string $resource = PayScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
