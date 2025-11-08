<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PayrollExporter;
use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayrollResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationGroup = 'Payroll and Compensation';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view_any_payroll');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete_any',
            'generatePayslip',
            'recalculate',
        ];
    }

    public static function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Payroll Slip')
                    ->schema([
                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('more_config_name')
                                    ->label('Company Name')
                                    ->weight('bold')
                                    ->columnSpan(2)
                                    ->formatStateUsing(fn () => 'Laravel App'),
                                Infolists\Components\TextEntry::make('id')
                                    ->label('Payroll ID')
                                    ->color('gray'),
                            ])
                            ->columns(3),
                        Infolists\Components\Fieldset::make('Employee Information')
                            ->schema([
                                Infolists\Components\TextEntry::make('employee.name')
                                    ->label('Employee Name')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('month')
                                    ->date('F Y')
                                    ->label('Payroll Month'),
                                Infolists\Components\TextEntry::make('employee.department.name')
                                    ->label('Department'),
                            ])
                            ->columns(3),
                        Infolists\Components\Fieldset::make('Salary Breakdown')
                            ->schema([
                                Infolists\Components\TextEntry::make('basic_salary')
                                    ->color('slate')
                                    ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                                    ->label('Basic Salary'),
                                Infolists\Components\TextEntry::make('total_allowances')
                                    ->color('emerald')
                                    ->formatStateUsing(fn ($state) => $state ? '+ ₦ '.number_format($state, 2) : '₦ 0.00')
                                    ->label('Total Allowances'),
                                Infolists\Components\TextEntry::make('total_bonuses')
                                    ->color('emerald')
                                    ->formatStateUsing(fn ($state) => $state ? '+ ₦ '.number_format($state, 2) : '₦ 0.00')
                                    ->label('Total Bonuses'),
                                Infolists\Components\TextEntry::make('total_deductions')
                                    ->color('rose')
                                    ->formatStateUsing(fn ($state) => $state ? '- ₦ '.number_format($state, 2) : '₦ 0.00')
                                    ->label('Total Deductions'),
                                Infolists\Components\TextEntry::make('net_salary')
                                    ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                                    ->label('Net Salary'),
                            ])
                            ->columns(['md' => 2]),
                        Infolists\Components\Fieldset::make('Allowances')
                            ->schema([
                                RepeatableEntry::make('current_allowances')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Allowance Type'),
                                        Infolists\Components\TextEntry::make('amount')
                                            ->money('NGN'),
                                    ]),
                            ]),
                        Infolists\Components\Fieldset::make('Deductions')
                            ->visible(fn ($record) => $record->current_deductions->isNotEmpty())
                            ->schema([
                                RepeatableEntry::make('current_deductions')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Deduction Type'),
                                        Infolists\Components\TextEntry::make('amount')
                                            ->money('NGN')
                                            ->color('danger'),
                                    ])
                                    ->grid(['md' => 2])
                                    ->columnSpan('full'),
                            ]),
                        Infolists\Components\Fieldset::make('Bonuses')
                            ->visible(fn ($record) => $record->current_bonuses?->isNotEmpty())
                            ->schema([
                                RepeatableEntry::make('current_bonuses')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Bonus Type'),
                                        Infolists\Components\TextEntry::make('amount')
                                            ->money('NGN')
                                            ->color('success'),
                                    ])
                                    ->columnSpan('full'),
                            ]),
                        Infolists\Components\Fieldset::make('')
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime('d M, Y H:i A')
                                    ->label('Generated On')
                                    ->color('gray'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Payroll Status')
                                    ->badge()
                                    ->color(fn ($state) => $state === 'Paid' ? 'success' : 'warning'),
                            ])->columns(['md' => 2]),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('employee_id')
                    ->required()
                    ->numeric(),
                Flatpickr::make('month')
                    ->required()
                    ->monthSelect(),
                Forms\Components\TextInput::make('basic_salary')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_allowances')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_deductions')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('net_salary')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('employee.branch.name')
                    ->sortable()
                    ->toggleable(),
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
                Tables\Filters\Filter::make('payroll_month')
                    ->form([
                        Flatpickr::make('month')
                            ->required()
                            ->default(now()->format('Y-m'))
                            ->maxDate(now()->format('Y-m'))
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Paid' => 'Paid',
                    ])
                    ->query(fn (Builder $query, array $data) => $data['value'] ? $query->where('status', $data['value']) : $query
                    ),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (! auth()->user()->can('view_outside_branch_employee')) {
                    return $query->join('employees', 'payrolls.employee_id', '=', 'employees.id')
                        ->where('employees.branch_id', auth()->user()->employee->branch->id);
                }
            })
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(PayrollExporter::class)
                    ->label('Export All')
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('downloadPDF')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('payroll.download-pdf', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Payroll $record) => $record->status != 'Pending'),
            ])
            ->bulkActions([
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(PayrollExporter::class)
                    ->label('Export Selected')
                    ->formats([
                        ExportFormat::Xlsx,
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
            'index' => Pages\ListPayrolls::route('/'),
            'view' => Pages\ViewPayroll::route('/{record}'),
        ];
    }
}
