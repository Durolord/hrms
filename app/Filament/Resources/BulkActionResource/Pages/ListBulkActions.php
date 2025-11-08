<?php

namespace App\Filament\Resources\BulkActionResource\Pages;

use App\Filament\Resources\BulkActionResource;
use Filament\Resources\Pages\ListRecords;

class ListBulkActions extends ListRecords
{
    protected static string $resource = BulkActionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()->can('view_any_bulk::action');
    }
}
