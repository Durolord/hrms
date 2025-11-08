<?php

namespace App\Filament\Resources;

use App\Filament\Exports\EmployeeExporter;
use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Jobs\Payrolls;
use App\Models\Bank;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Bytexr\QueueableBulkActions\Filament\Actions\QueueableBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class EmployeeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return EmployeeResource::getUrl('view', ['record' => $record]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'email',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Employee $record */
        return ['email' => $record->email];
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('edit')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'export',
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'view_outside_branch',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->disabledOn('edit')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->disabledOn('edit')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel(),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('designation_id')
                    ->relationship('designation', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('bank_id')
                    ->label('Bank')
                    ->options(Bank::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-m-arrow-path')
                                    ->tooltip('Generate code based on name')
                                    ->action(function (callable $get, callable $set) {
                                        $name = $get('name');
                                        $generatedCode = Str::slug($name);
                                        $set('code', $generatedCode);
                                    })
                            )
                            ->unique(table: 'banks', column: 'code', ignoreRecord: true),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $bank = Bank::create($data);

                        return $bank->id;
                    })
                    ->nullable(),
                Forms\Components\TextInput::make('account_number')
                    ->label('Account Number')
                    ->numeric()
                    ->minLength(10)
                    ->maxLength(10),
                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->live()
                    ->required(),
                Forms\Components\Select::make('manager_id')
                    ->live()
                    ->relationship(
                        'manager',
                        'name',
                        fn ($query, $get) => $query
                            ->where('branch_id', $get('branch_id'))
                            ->whereHas('user', fn ($q) => $q->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('name', [
                                'Admin', 'HR Manager', 'Finance Manager',
                                'Department Head', 'Team Lead', 'Auditor', 'IT Admin',
                            ])
                            )
                            )
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\DatePicker::make('employment_start_date')
                    ->disabledOn('edit')
                    ->default(today())
                    ->native(false)
                    ->closeOnDateSelection()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('designation.name')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank.name')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('active')
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
            ->filters([
                SelectFilter::make('department')->relationship('department', 'name'),
                SelectFilter::make('designation')->relationship('designation', 'name'),
                SelectFilter::make('branch')->relationship('branch', 'name'),
                SelectFilter::make('active')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->label('Employment Status'),
                Filter::make('employment_start_date')
                    ->label('Start Date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('to'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn ($q) => $q->whereDate('employment_start_date', '>=', $data['from']))
                        ->when($data['to'], fn ($q) => $q->whereDate('employment_start_date', '<=', $data['to']))
                    ),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (! auth()->user()->can('view_outside_branch_employee')) {
                    return $query->where('branch_id', auth()->user()->employee->branch->id);
                }
            })
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(EmployeeExporter::class)
                    ->label('Export All')
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
            ])
            ->bulkActions([
                QueueableBulkAction::make('delete_user')
                    ->label('Delete selected')
                    ->job(Payrolls::class),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('Activate Employees')->action(fn ($records) => $records->each(fn ($record) => $record->update(['active' => true]))),
                    Tables\Actions\BulkAction::make('Deactivate Employees')->action(fn ($records) => $records->each(fn ($record) => $record->update(['active' => false]))),
                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(EmployeeExporter::class)
                    ->label('Export Selected')
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
            ]);
    }

    public static function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('email'),
                        Infolists\Components\TextEntry::make('phone'),
                        Infolists\Components\TextEntry::make('employment_start_date')
                            ->date('M d, Y'),
                        Infolists\Components\TextEntry::make('bank.name'),
                        Infolists\Components\TextEntry::make('account_number'),
                    ]),
                Infolists\Components\Section::make('Employment Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('department.name'),
                        Infolists\Components\TextEntry::make('designation.name'),
                        Infolists\Components\TextEntry::make('manager.name'),
                        Infolists\Components\TextEntry::make('branch.name'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Payrolls', [
                RelationManagers\PayrollsRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            EmployeeResource\Widgets\EmployeeStatsWidget::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
