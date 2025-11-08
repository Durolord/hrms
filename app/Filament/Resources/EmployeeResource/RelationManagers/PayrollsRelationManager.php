<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Filament\Exports\PayrollExporter;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollsRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('month')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('month')
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->date('F Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->numeric()
                    ->sortable()
                    ->color('slate')
                    ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_allowances')
                    ->numeric()
                    ->sortable()
                    ->color('emerald')
                    ->formatStateUsing(fn ($state) => $state ? '+ ₦ '.number_format($state, 2) : '₦ 0.00')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_deductions')
                    ->numeric()
                    ->sortable()
                    ->color('rose')
                    ->formatStateUsing(fn ($state) => $state ? '- ₦ '.number_format($state, 2) : '₦ 0.00')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('net_salary')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(PayrollExporter::class)
                    ->label('Export All')
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(PayrollExporter::class)
                    ->label('Export Selected')
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
            ]);
    }
}
