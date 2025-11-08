<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use App\Models\Leave;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewLeave extends ViewRecord
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->tooltip('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(fn (Leave $record) => self::approveLeave($record))
                ->visible(fn (Leave $record) => auth()->user()->canApproveLeave($record) && $record->status === 'Pending'),
            Actions\Action::make('reject')
                ->tooltip('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(fn (Leave $record) => self::rejectLeave($record))
                ->visible(fn (Leave $record) => auth()->user()->canApproveLeave($record) && $record->status === 'Pending'),
        ];
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
}
