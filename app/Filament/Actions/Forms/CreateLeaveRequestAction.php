<?php

namespace App\Filament\Actions\Forms;

use App\Models\Leave;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class CreateLeaveRequestAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'createLeaveRequest';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this
            ->modalHeading('Create Leave Request')
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
            ->action(function (array $data): void {
                $employee = auth()->user()->employee;
                $data['employee_id'] = $employee->id;
                $data['status'] = 'Pending';
                $data['deducted_from_payroll'] = false;
                Leave::create($data);
                Notification::make()
                    ->title('Leave Request Created')
                    ->success()
                    ->send();
            })
            ->model(Leave::class);
    }
}
