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
            Actions\Action::make('nextStep')
                ->tooltip('Proceed to next step')
                ->icon('heroicon-o-forward')
                ->color('cyan')
                ->label(fn (Payroll $record) => $this->getNextStepLabel($record))
                ->visible(fn (Payroll $record) => $record->status !== 'Paid')
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
            $payroll->status = 'Approved';
            $message = 'Payroll successfully regenerated and status updated to Approved.';
        } elseif ($payroll->status === 'Approved') {
            $payroll->status = 'Paid';
            $message = 'Payroll status updated to Paid.';
        } else {
            Notification::make()
                ->title('Payroll is already Paid.')
                ->danger()
                ->send();

            return;
        }
        $payroll->save();
        Notification::make()
            ->title($message)
            ->success()
            ->send();
    }
}
