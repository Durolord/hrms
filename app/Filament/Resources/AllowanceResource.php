<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AllowanceResource\Pages;
use App\Models\Allowance;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class AllowanceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Allowance::class;

    protected static ?string $navigationGroup = 'Payroll and Compensation';

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('create_allowance');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'import',
            'export',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pay_scale_id')
                    ->relationship('pay_scale', 'name')
                    ->required(),
                Forms\Components\Toggle::make('is_percentage')
                    ->label('Is Percentage?')
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('amount', 0)),
                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->mask(RawJs::make('$money($input)'))
                    ->default(0)
                    ->minValue(0)
                    ->live()
                    ->maxValue(fn (Get $get) => $get('is_percentage') ? 50 : null)
                    ->helperText(fn (Get $get) => $get('is_percentage') ? 'Max 50% allowed' : ''),
                Flatpickr::make('month')
                    ->required()
                    ->monthSelect(),
                Forms\Components\Textarea::make('reason'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pay_scale.name')
                    ->sortable()
                    ->url(fn ($record): string => route('filament.admin.resources.pay-scales.view', ['record' => $record->pay_scale_id]))
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reason')->limit(50),
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
            'index' => Pages\ListAllowances::route('/'),
            'create' => Pages\CreateAllowance::route('/create'),
            'view' => Pages\ViewAllowance::route('/{record}'),
            'edit' => Pages\EditAllowance::route('/{record}/edit'),
        ];
    }
}
