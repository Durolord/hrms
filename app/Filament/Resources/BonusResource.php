<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusResource\Pages;
use App\Models\Bonus;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BonusResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Bonus::class;

    protected static ?string $navigationGroup = 'Payroll and Compensation';

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
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
                Forms\Components\Select::make('employee_id')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => auth()->user()->can('view_outside_branch_employee')
                            ? $query
                            : $query->where('branch_id', auth()->user()->employee->branch->id),
                    )
                    ->searchable()
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
                    ->default(0)
                    ->live()
                    ->maxValue(fn (Get $get) => $get('is_percentage') ? 50 : null)
                    ->helperText(fn (Get $get) => $get('is_percentage') ? 'Max 50% allowed' : ''),
                Flatpickr::make('month')
                    ->monthSelect()
                    ->dateFormat('Y-m')
                    ->maxDate(now()->startOfMonth())
                    ->required(),
                Forms\Components\TextInput::make('reason')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable()
                    ->color('emerald')
                    ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('month')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (! auth()->user()->can('view_outside_branch_employee')) {
                    return $query->join('employees', 'bonuses.employee_id', '=', 'employees.id')
                        ->where('employees.branch_id', auth()->user()->employee->branch->id);
                }
            })
            ->filters([
            ])
            ->actions([
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
            'index' => Pages\ListBonuses::route('/'),
            'create' => Pages\CreateBonus::route('/create'),
            'edit' => Pages\EditBonus::route('/{record}/edit'),
        ];
    }
}
