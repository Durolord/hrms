<?php

namespace App\Notifications;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PayslipReady extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payroll $payroll) {}

    public function via($notifiable): array
    {
        return method_exists($notifiable, 'wantsEmailNotifications') && ! $notifiable->wantsEmailNotifications()
            ? ['database']
            : ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $month = $this->payroll->month->format('F Y');
        $filename = Str::slug($this->payroll->employee->name).'-'.$this->payroll->month->format('F-Y').'.pdf';

        return (new MailMessage)
            ->subject("Your payslip for {$month}")
            ->greeting("Hello {$this->payroll->employee->name},")
            ->line("Your payroll for {$month} has been approved. Your payslip is attached.")
            ->attachData(
                Pdf::loadView('pdf.payroll-slip', ['payroll' => $this->payroll])->output(),
                $filename,
                ['mime' => 'application/pdf']
            );
    }

    public function toDatabase($notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title('Payslip Ready')
            ->body("Your payroll for {$this->payroll->month->format('F Y')} has been approved.")
            ->success()
            ->actions([
                \Filament\Notifications\Actions\Action::make('View')
                    ->link()
                    ->url(route('filament.admin.resources.payrolls.view', $this->payroll->id), shouldOpenInNewTab: true)
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
