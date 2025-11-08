<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayScaleResource\Pages;
use App\Models\PayScale;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class PayScaleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PayScale::class;

    protected static ?string $navigationGroup = 'Payroll and Compensation';

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    public static function getPermissionPrefixes(): array
    {
        return [
            'activate',
            'deactivate',
            'linkDesignations',
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'export',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('basic_salary')
                    ->required()
                    ->prefix('₦')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric(),
                Forms\Components\Toggle::make('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->numeric()
                    ->prefix('₦ ')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayScales::route('/'),
            'create' => Pages\CreatePayScale::route('/create'),
            'view' => Pages\ViewPayScale::route('/{record}'),
            'edit' => Pages\EditPayScale::route('/{record}/edit'),
        ];
    }
}
