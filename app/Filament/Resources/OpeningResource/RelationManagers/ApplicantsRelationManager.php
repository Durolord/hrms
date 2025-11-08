<?php

namespace App\Filament\Resources\OpeningResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicantsRelationManager extends RelationManager
{
    protected static string $relationship = 'applicants';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
            ])
            ->headerActions([
                Tables\Actions\Action::make('hire')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(function ($record) {
                        $record->update(['status' => 'hired']);
                    })
                    ->visible(fn ($record): bool => auth()->user()->can('moveStage') &&
                        $record->status !== 'Applied'
                    ),
                Tables\Actions\Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                    })
                    ->visible(fn ($record): bool => auth()->user()->can('moveStage') &&
                        $record->status == 'Applied'
                    ),
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
