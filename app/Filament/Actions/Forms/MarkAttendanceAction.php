<?php

namespace App\Filament\Actions\Forms;

use App\Filament\Loggers\AttendanceLogger;
use App\Models\Attendance;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class MarkAttendanceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'markAttendance';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this
            ->label('Mark Attendance')
            ->modalHeading("Mark Today's Attendance")
            ->modalButton('Save Attendance')
            ->form([
                Forms\Components\DatePicker::make('date')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->hidden()
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
            ->fillForm(function () {
                $employeeId = Auth::user()->employee->id ?? null;
                $currentDate = now()->toDateString();
                if (! $employeeId) {
                    return [
                        'date' => $currentDate,
                    ];
                }
                $attendance = Attendance::where('employee_id', $employeeId)
                    ->whereDate('date', $currentDate)
                    ->first();
                if ($attendance) {
                    return [
                        'date' => $attendance->date,
                        'time_in' => $attendance->time_in,
                        'time_out' => $attendance->time_out,
                        'break_start' => $attendance->break_start,
                        'break_end' => $attendance->break_end,
                    ];
                }

                return [
                    'date' => $currentDate,
                ];
            })
            ->action(function (array $data) {
                $employeeId = Auth::user()->employee->id ?? null;
                $currentDate = now()->toDateString();
                if (! $employeeId) {
                    \Filament\Notifications\Notification::make()
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
            })
            ->successNotificationTitle('Attendance Saved Successfully!');
    }
}
