<?php

namespace App\Filament\Resources\LeaveResource\Pages;

use App\Filament\Resources\LeaveResource;
use App\Models\Leave;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
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
                ->requiresConfirmation()
                ->action(function (Leave $record) {
                    $record->approve(auth()->user());
                    $this->refreshFormData(['status', 'approver_id', 'approved_on']);
                })
                ->visible(fn (Leave $record) => auth()->user()->canApproveLeave($record) && $record->status === 'Pending'),
            Actions\Action::make('reject')
                ->tooltip('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('rejection_reason')->label('Reason')->required(),
                ])
                ->action(function (Leave $record, array $data) {
                    $record->reject(auth()->user(), $data['rejection_reason']);
                    $this->refreshFormData(['status', 'approver_id', 'approved_on', 'rejection_reason']);
                })
                ->visible(fn (Leave $record) => auth()->user()->canApproveLeave($record) && $record->status === 'Pending'),
            Actions\Action::make('cancel')
                ->label('Cancel request')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (Leave $record) {
                    $record->delete();
                    $this->redirect(LeaveResource::getUrl('index'));
                })
                ->visible(fn (Leave $record) => $record->status === 'Pending'
                    && $record->employee->user_id === auth()->id()),
        ];
    }
}
