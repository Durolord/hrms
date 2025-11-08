<?php

namespace App\Filament\Resources\OpeningResource\Pages;

use App\Filament\Resources\OpeningResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOpenings extends ListRecords
{
    protected static string $resource = OpeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()->can('create_opening');
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make()
                ->label('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)->orderBy('created_at', 'desc')),
            'inactive' => Tab::make()
                ->label('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)->orderBy('created_at', 'desc')),
        ];
    }
}
