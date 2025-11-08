<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeductionResource\Pages;
use App\Models\Deduction;
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

class DeductionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Deduction::class;

    protected static ?string $navigationGroup = 'Payroll and Compensation';

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';

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
                    ->altInput(true)
                    ->altFormat('F j, Y')
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
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                    ->color('rose')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('month')
                    ->date()
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
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => auth()->user()->can('view_outside_branch_employee')
                            ? $query
                            : $query->where('branch_id', auth()->user()->employee->branch->id),
                    ),
                Tables\Filters\Filter::make('deduction_month')
                    ->form([
                    Flatpickr::make('month')
                        ->monthSelect(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'],
                                fn (Builder $query, $date): Builder => $query->whereMonth('month', '=', \Carbon\Carbon::parse($date)->month)
                                    ->whereYear('month', '=', \Carbon\Carbon::parse($date)->year),
                            );
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (! auth()->user()->can('view_outside_branch_employee')) {
                    return $query->join('employees', 'deductions.employee_id', '=', 'employees.id')
                        ->where('employees.branch_id', auth()->user()->employee->branch->id);
                }
            })
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
            'index' => Pages\ListDeductions::route('/'),
            'create' => Pages\CreateDeduction::route('/create'),
            'edit' => Pages\EditDeduction::route('/{record}/edit'),
        ];
    }
}
