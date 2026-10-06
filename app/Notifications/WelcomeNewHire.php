<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNewHire extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $setPasswordUrl, public string $role) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to '.config('app.name'))
            ->greeting("Welcome, {$notifiable->name}!")
            ->line("You have been hired as {$this->role}. An account has been created for you.")
            ->line('Set your password to sign in. This link expires in '.config('auth.passwords.users.expire', 60).' minutes.')
            ->action('Set your password', $this->setPasswordUrl);
    }
}
