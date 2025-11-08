<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BulkActionResource\Pages;
use Bytexr\QueueableBulkActions\Enums\StatusEnum;
use Bytexr\QueueableBulkActions\Support\Config;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BulkActionResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getPluralModelLabel(): string
    {
        return __('queueable-bulk-actions::resource.plural_label');
    }

    protected static ?string $navigationGroup = 'Admin';

    public static function getModel(): string
    {
        return Config::bulkActionModel();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view_any_bulk_action');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->toggleable()
                    ->label('Action ID'),
                TextColumn::make('name')->toggleable(),
                TextColumn::make('status')->toggleable()
                    ->color(fn ($state) => Config::color($state))
                    ->badge()
                    ->formatStateUsing(fn (StatusEnum $state) => $state->getLabel()),
                TextColumn::make('message')->toggleable()->wrap()->placeholder('-'),
                TextColumn::make('total_records')->toggleable(),
                TextColumn::make('started_at')->toggleable()->dateTime()->placeholder('-'),
                TextColumn::make('failed_at')->toggleable()->dateTime()->placeholder('-'),
                TextColumn::make('finished_at')->toggleable()->dateTime()->placeholder('-'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBulkActions::route('/'),
            'view' => Pages\ViewBulkAction::route('/{record}'),
        ];
    }

    public function register(Panel $panel): void
    {
        if (Config::resource() != config('queueable-bulk-actions.model')) {
            $panel->resources([
                BulkActionResource::class,
            ]);
        }
    }
}
