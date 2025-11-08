<?php

namespace App\Notifications;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as LaravelNotification;

class UserNotification extends LaravelNotification
{
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public ?string $emailMessage = null,
        public array $channels = ['filament', 'email']
    ) {}

    public function via($notifiable): array
    {
        $via = [];
        if (in_array('email', $this->channels)) {
            $via[] = 'mail';
        }
        $via[] = 'database';

        return $via;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->emailMessage ?? $this->message)
            ->action('View', $this->url ?? url('/'))
            ->line('Thank you!');
    }

    /**
     * This ensures Laravel's database channel stores the correct format.
     */
    /**
     * This method ensures Filament notifications work properly.
     */
    public function toDatabase($notifiable): array
    {
        return Notification::make()
            ->title($this->title)
            ->body($this->message)
            ->success()
            ->actions([
                Action::make('View')
                    ->link()
                    ->url($this->url, shouldOpenInNewTab: true)
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    public function send($notifiable): void
    {
        if (in_array('filament', $this->channels)) {
            $this->toDatabase($notifiable);
        }
        if (in_array('email', $this->channels)) {
            $notifiable->notify($this);
        }
    }
}
