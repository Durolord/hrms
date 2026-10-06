<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Plain email to an applicant (who has no user account): confirmation, interview invite, outcome.
 */
class ApplicantUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param  list<string>  $lines */
    public function __construct(
        public string $subject,
        public string $name,
        public array $lines,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->subject)->greeting("Hello {$this->name},");
        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        return $mail->salutation('Kind regards, '.config('app.name'));
    }
}
