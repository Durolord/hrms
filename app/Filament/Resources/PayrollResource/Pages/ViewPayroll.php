<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Models\Payroll;
use App\Services\PayrollProcessingService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayroll extends ViewRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('regenerate')
                ->tooltip('Regenerate Payroll')
                ->icon('heroicon-o-arrow-path')
                ->color('teal')
                ->requiresConfirmation()
                ->visible(fn (Payroll $record) => $record->status === 'Pending')
                ->action(fn (Payroll $record) => $this->regeneratePayroll($record)),
            Actions\Action::make('reject')
                ->tooltip('Reject payroll')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Payroll $record) => $record->status === 'Pending')
                ->action(function (Payroll $record) {
                    $record->update(['status' => 'Rejected']);
                    Notification::make()->title('Payroll rejected.')->success()->send();
                }),
            Actions\Action::make('nextStep')
                ->tooltip('Proceed to next step')
                ->icon('heroicon-o-forward')
                ->color('cyan')
                ->label(fn (Payroll $record) => $this->getNextStepLabel($record))
                ->visible(fn (Payroll $record) => in_array($record->status, ['Pending', 'Approved']))
                ->action(fn (Payroll $record) => $this->toggleStatus($record)),
        ];
    }

    protected function regeneratePayroll(Payroll $payroll): bool
    {
        try {
            $payrollService = new PayrollProcessingService;
            $payrollService->processPayrollForEmployee($payroll->employee, [
                'month' => $payroll->month->format('Y-m'),
            ]);
            Notification::make()
                ->title('Payroll successfully regenerated.')
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to regenerate payroll: '.$e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    protected function getNextStepLabel(Payroll $payroll): string
    {
        return $payroll->status === 'Pending' ? 'Approve' : 'Pay';
    }

    protected function toggleStatus(Payroll $payroll): void
    {
        if ($payroll->status === 'Pending') {
            if (! $this->regeneratePayroll($payroll)) {
                return;
            }
            $payroll->refresh()->markApproved();
            $message = 'Payroll successfully regenerated and approved. The payslip has been emailed to the employee.';
        } elseif ($payroll->status === 'Approved') {
            $payroll->markPaid();
            $message = 'Payroll marked as Paid.';
        } else {
            Notification::make()
                ->title('Payroll is already Paid.')
                ->danger()
                ->send();

            return;
        }
        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }
}
