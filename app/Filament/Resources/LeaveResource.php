<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Leave;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $employee = $user->employee;
        $query = static::getModel()::query()->where('status', 'Pending');
        if ($user->can('view_non_managed_leave')) {
            if (! $user->can('view_outside_branch_employee')) {
                $query->join('employees', 'leaves.employee_id', '=', 'employees.id')
                    ->where('employees.branch_id', $employee->branch->id);
            }
        } else {
            if ($user->can('view_outside_branch_employee')) {
                $query->join('employees', 'leaves.employee_id', '=', 'employees.id')
                    ->where('employees.manager_id', $employee->id);
            } else {
                $query->join('employees', 'leaves.employee_id', '=', 'employees.id')
                    ->where('employees.branch_id', $employee->branch->id)
                    ->where('employees.manager_id', $employee->id);
            }
        }

        return $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    protected static ?string $navigationBadgeTooltip = 'The number of pending leaves';

    public static function getPermissionPrefixes(): array
    {
        return [
            'approve',
            'reject',
            'override',
            'retroactive',
            'bulkApprove',
            'export',
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'view_outside_branch',
            'view_non_managed',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('employee.name')->label('Employee'),
                Infolists\Components\TextEntry::make('leave_type.name')->label('Leave Type'),
                Infolists\Components\TextEntry::make('start_date')->label('Start Date')->date(),
                Infolists\Components\TextEntry::make('end_date')->label('End Date')->date(),
                Infolists\Components\TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                    }),
                Infolists\Components\TextEntry::make('approver.name')->label('Approver'),
                Infolists\Components\TextEntry::make('created_at')->label('Requested On')->dateTime(),
            ])
            ->columns(2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();
                            $employee = $user->employee;
                            if ($user->can('view_non_managed_leave')) {
                                if ($user->can('view_outside_branch_employee')) {
                                    return $query;
                                }

                                return $query->where('branch_id', $employee->branch->id);
                            }
                            if ($user->can('view_outside_branch_employee')) {
                                return $query->where('manager_id', $employee->id);
                            }

                            return $query->where('branch_id', $employee->branch->id)
                                ->where('manager_id', $employee->id);
                        },
                    )
                    ->label('Employee')
                    ->preload()
                    ->searchable(['name', 'email'])
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->native(false)
                    ->minDate(now()->startOfDay())
                    ->closeOnDateSelection()
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->native(false)
                    ->minDate(now()->addDays(1))
                    ->closeOnDateSelection()
                    ->required(),
                Forms\Components\Select::make('leave_type_id')
                    ->relationship('leave_type', 'name')
                    ->required()
                    ->label('Type'),
            ]);
    }

    public static function query(Builder $query): Builder
    {
        return $query->whereHas('employee', function ($query) {
            $query->where('user_id', '!=', auth()->id());
        });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_date')
                    ->toggleable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->toggleable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->toggleable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('leave_balance')
                    ->label('Current Leave Balance')
                    ->getStateUsing(fn ($record) => $record->status === 'Pending'
                            ? ($record->leaveBalance() ?? 'N/A')
                            : null
                    )
                    ->hidden(fn ($livewire) => $livewire->activeTab !== 'pending')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->toggleable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('leave_type.name')
                    ->toggleable()
                    ->label('Leave Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('line_manager')
                    ->toggleable()
                    ->label('Line Manager')
                    ->getStateUsing(fn ($record) => $record->employee?->manager?->name ?? 'N/A'),
                Tables\Columns\TextColumn::make('approver.name')
                    ->toggleable()
                    ->numeric()
                    ->hidden(fn ($livewire) => $livewire->activeTab !== 'approved')
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_on')
                    ->toggleable()
                    ->date()
                    ->hidden(fn ($livewire) => $livewire->activeTab !== 'approved')
                    ->sortable(),
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
                $user = auth()->user();
                $employee = $user->employee;
                $query->join('employees', 'leaves.employee_id', '=', 'employees.id');
                if ($user->can('view_non_managed_leave')) {
                    if (! $user->can('view_outside_branch_employee')) {
                        $query->where('employees.branch_id', $employee->branch->id);
                    }
                } else {
                    if ($user->can('view_outside_branch_employee')) {
                        $query->where('employees.manager_id', $employee->id);
                    } else {
                        $query->where('employees.branch_id', $employee->branch->id)
                            ->where('employees.manager_id', $employee->id);
                    }
                }

                return $query;
            })
            ->filters([
                SelectFilter::make('leaveType')
                    ->relationship('leave_type', 'name')
                    ->label('Leave Type'),
                SelectFilter::make('employee')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();
                            $employee = $user->employee;
                            if ($user->can('view_non_managed_leave')) {
                                if ($user->can('view_outside_branch_employee')) {
                                    return $query;
                                }

                                return $query->where('branch_id', $employee->branch->id);
                            }
                            if ($user->can('view_outside_branch_employee')) {
                                return $query->where('manager_id', $employee->id);
                            }

                            return $query->where('branch_id', $employee->branch->id)
                                ->where('manager_id', $employee->id);
                        },
                    )
                    ->searchable()
                    ->label('Employee'),
                Filter::make('start_date')
                    ->label('Leave Start Date')
                    ->form([
                    Forms\Components\DatePicker::make('from'),
                    Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'], fn ($q) => $q->whereDate('start_date', '>=', $data['from']))
                    ->when($data['to'], fn ($q) => $q->whereDate('end_date', '<=', $data['to']))
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->tooltip('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (Leave $record) => $record->update(['status' => 'Approved']))
                    ->visible(fn (Leave $record) => auth()->user()->can('approve_leave') && $record->status === 'Pending'),
                Tables\Actions\Action::make('reject')
                    ->tooltip('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Leave $record) => $record->update(['status' => 'Rejected']))
                    ->visible(fn (Leave $record) => auth()->user()->can('reject_leave') && $record->status === 'Pending'),
            ])
            ->bulkActions([
                BulkAction::make('Bulk Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($records) => $records->each(fn ($record) => $record->update(['status' => 'Approved'])))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('Bulk Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn ($records) => $records->each(fn ($record) => $record->update(['status' => 'Rejected'])))
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    private static function approveLeave(Leave $record)
    {
        $record->update([
            'status' => 'Approved',
            'approved_on' => now(),
            'approver_id' => auth()->id(),
        ]);
        if ($record->employee->user) {
            Notification::make()
                ->title('Leave Approved')
                ->body('Your '.strtolower($record->leave_type->name)." request from {$record->start_date->format('d M Y')} to {$record->end_date->format('d M Y')} has been approved.")
                ->success()
                ->sendToDatabase($record->employee->user);
        }
        $approver = auth()->user();
        if ($approver && $approver->hasRole('Admin')) {
            User::role('Admin')->each(fn ($admin) => Notification::make()
                ->title('Leave Approved for Subordinate')
                ->body('The '.strtolower($record->leave_type->name)." request from {$record->start_date->format('d M Y')} to {$record->end_date->format('d M Y')} has been approved for {$record->employee->name}.")
                ->success()
                ->sendToDatabase($admin));
        } else {
            if ($record->employee->manager && $record->employee->manager->user) {
                Notification::make()
                    ->title('Leave Approved for Subordinate')
                    ->body('The '.strtolower($record->leave_type->name)." request from {$record->start_date->format('d M Y')} to {$record->end_date->format('d M Y')} has been approved for {$record->employee->name}.")
                    ->success()
                    ->sendToDatabase($record->employee->manager->user);
            }
        }
    }

    private static function rejectLeave(Leave $record)
    {
        $record->update([
            'status' => 'Rejected',
            'approver_id' => auth()->id(),
        ]);
        if ($record->employee->user) {
            Notification::make()
                ->title('Leave Rejected')
                ->body('Your '.strtolower($record->leave_type->name)." request from {$record->start_date->format('d M Y')} to {$record->end_date->format('d M Y')} has been rejected.")
                ->danger()
                ->sendToDatabase($record->employee->user);
        }
        $approver = auth()->user();
        if ($approver && $approver->hasRole('Admin')) {
            User::role('Admin')->each(fn ($admin) => Notification::make()
                ->title('Leave Rejected for Subordinate')
                ->body('The '.strtolower($record->leave_type->name)." request from {$record->start_date->format('d M Y')} to {$record->end_date->format('d M Y')} has been rejected for {$record->employee->name}.")
                ->danger()
                ->sendToDatabase($admin));
        } else {
            if ($record->employee->manager && $record->employee->manager->user) {
                Notification::make()
                    ->title('Leave Rejected for Subordinate')
                    ->body('The '.strtolower($record->leave_type->name)." request from {$record->start_date->format('d M Y')} to {$record->end_date->format('d M Y')} has been rejected for {$record->employee->name}.")
                    ->danger()
                    ->sendToDatabase($record->employee->manager->user);
            }
        }
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getWidgets(): array
    {
        return [
            LeaveResource\Widgets\LeaveCalendarWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'view' => Pages\ViewLeave::route('/{record}'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
