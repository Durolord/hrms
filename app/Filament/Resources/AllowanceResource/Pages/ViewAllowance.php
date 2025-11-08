<?php

namespace App\Filament\Resources\AllowanceResource\Pages;

use App\Filament\Resources\AllowanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAllowance extends ViewRecord
{
    protected static string $resource = AllowanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
