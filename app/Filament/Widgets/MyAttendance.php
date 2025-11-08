<?php

namespace App\Filament\Widgets;

use App\Filament\Loggers\AttendanceLogger;
use App\Filament\Widgets\CollapsibleTableWidget as BaseWidget;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MyAttendance extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->defaultSort('created_at', 'desc')
            ->headerActions($this->getTableHeaderActions());
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $employee = Auth::user()->employee;
        if (! $employee) {
            return Attendance::query()->whereRaw('0 = 1');
        }

        return Attendance::query()->where('employee_id', $employee->id);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('date')
                ->toggleable()
                ->date(),
            Tables\Columns\TextColumn::make('time_in')
                ->toggleable()
                ->dateTime('h:i A'),
            Tables\Columns\TextColumn::make('time_out')
                ->toggleable()
                ->dateTime('h:i A'),
            Tables\Columns\TextColumn::make('totalBreakTime')
                ->suffix(' minutes'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make('attendance')
                ->label('Mark Attendance')
                ->icon('heroicon-o-clock')
                ->modalHeading("Mark Today's Attendance")
                ->modalButton('Save Attendance')
                ->form([
                    Forms\Components\DatePicker::make('date')
                        ->native(false)
                        ->closeOnDateSelection()
                        ->disabled()
                        ->default(now()->toDateString())
                        ->label('Date'),
                    Forms\Components\TimePicker::make('time_in')
                        ->label('Time In')
                        ->displayFormat('h:i A')
                        ->seconds(false)
                        ->required(),
                    Forms\Components\TimePicker::make('time_out')
                        ->label('Time Out')
                        ->seconds(false),
                    Forms\Components\TimePicker::make('break_start')
                        ->label('Break Start')
                        ->seconds(false),
                    Forms\Components\TimePicker::make('break_end')
                        ->label('Break End')
                        ->seconds(false),
                ])
                ->fillForm(fn () => $this->getAttendanceData())
                ->action(fn (array $data) => $this->saveAttendance($data))
                ->successNotificationTitle('Attendance Saved Successfully!'),
        ];
    }

    protected function getAttendanceData(): array
    {
        $employeeId = Auth::user()->employee->id ?? null;
        $currentDate = now()->toDateString();
        if (! $employeeId) {
            return ['date' => $currentDate];
        }
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $currentDate)
            ->first();

        return $attendance
            ? [
                'date' => $attendance->date,
                'time_in' => $attendance->time_in,
                'time_out' => $attendance->time_out,
                'break_start' => $attendance->break_start,
                'break_end' => $attendance->break_end,
            ]
            : ['date' => $currentDate];
    }

    protected function saveAttendance(array $data): void
    {
        $employeeId = Auth::user()->employee->id ?? null;
        $currentDate = now()->toDateString();
        if (! $employeeId) {
            Notification::make()
                ->title('Employee not found')
                ->danger()
                ->send();

            return;
        }
        $attendance = Attendance::firstOrNew([
            'employee_id' => $employeeId,
            'date' => $currentDate,
        ]);
        $isNew = ! $attendance->exists;
        $oldAttendance = $isNew ? null : $attendance->replicate();
        $attendance->time_in = $data['time_in'];
        $attendance->time_out = $data['time_out'] ?? $attendance->time_out;
        $attendance->break_start = $data['break_start'] ?? $attendance->break_start;
        $attendance->break_end = $data['break_end'] ?? $attendance->break_end;
        $attendance->save();
        if ($isNew) {
            AttendanceLogger::make($attendance)->created();
        } else {
            AttendanceLogger::make($oldAttendance, $attendance)->updated();
        }
        Notification::make()
            ->title('Attendance Saved Successfully!')
            ->success()
            ->send();
    }
}
