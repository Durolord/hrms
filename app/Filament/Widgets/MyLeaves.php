<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\CollapsibleTableWidget as BaseWidget;
use App\Models\Leave;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyLeaves extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->headerActions($this->getTableHeaderActions())
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $employee = Auth::user()->employee;
        if (! $employee) {
            return Leave::query()->whereRaw('0 = 1');
        }

        return Leave::query()->where('employee_id', $employee->id);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('start_date')
                ->date()
                ->toggleable()
                ->label('Start Date'),
            Tables\Columns\TextColumn::make('end_date')
                ->date()
                ->toggleable()
                ->label('End Date'),
            Tables\Columns\TextColumn::make('approved_on')
                ->dateTime()
                ->toggleable()
                ->label('Approved On'),
            Tables\Columns\TextColumn::make('approver.name')
                ->toggleable()
                ->sortable()
                ->label('Approver'),
            Tables\Columns\TextColumn::make('status')
                ->label('Status'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('date_range')
                ->label('Date Range')
                ->form([
                    Flatpickr::make('filter_range')
                        ->range()
                        ->label('Select Date Range')
                        ->dateFormat('Y-m-d')
                        ->closeOnSelect(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (! isset($data['filter_range']) || empty($data['filter_range'])) {
                        return $query;
                    }
                    [$from, $to] = explode(' to ', $data['filter_range']);

                    return $query
                        ->whereDate('start_date', '>=', $from)
                        ->whereDate('end_date', '<=', $to);
                }),
            Tables\Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                ])
                ->query(fn (Builder $query, array $data) => $data['value'] ? $query->where('status', $data['value']) : $query
                ),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('leaveRequest')
                ->label('Make Request')
                ->icon('heroicon-o-calendar')
                ->modalHeading('Create Leave Request')
                ->modalButton('Submit Request')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->required(),
                    Select::make('leave_type_id')
                        ->label('Leave Type')
                        ->relationship('leave_type', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(fn (array $data) => $this->submitLeaveRequest($data))
                ->successNotificationTitle('Leave Request Created'),
        ];
    }

    protected function submitLeaveRequest(array $data): void
    {
        $employee = Auth::user()->employee;
        if (! $employee) {
            Notification::make()
                ->title('Error: Employee Not Found')
                ->danger()
                ->send();

            return;
        }
        Leave::create([
            'employee_id' => $employee->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'leave_type_id' => $data['leave_type_id'],
            'status' => 'Pending',
            'deducted_from_payroll' => false,
        ]);
        Notification::make()
            ->title('Leave Request Created')
            ->success()
            ->send();
    }
}
