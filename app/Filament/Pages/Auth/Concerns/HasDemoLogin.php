<?php

namespace App\Filament\Pages\Auth\Concerns;

use App\Support\Demo;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;

/**
 * Demo mode on the login page: pre-fills the first demo account and signs in as any listed demo account in one click.
 * The account list itself is rendered by the AUTH_LOGIN_FORM_AFTER hook in AdminPanelProvider.
 */
trait HasDemoLogin
{
    /**
     * Livewire runs this after the page's own mount(), which fills the form with blanks.
     */
    public function mountHasDemoLogin(): void
    {
        $first = Demo::accounts()->first();
        if (! Demo::enabled() || ! $first || Filament::auth()->check()) {
            return;
        }
        $this->form->fill(['email' => $first['email'], 'password' => Demo::password()]);
    }

    /**
     * One-click sign in. Only listed demo emails are accepted, and the normal authenticate() path
     * (throttling, credential check, session regeneration) does the rest.
     */
    public function loginAs(string $email): ?LoginResponse
    {
        if (! Demo::enabled() || ! Demo::isDemoEmail($email)) {
            Notification::make()
                ->title('Only the listed demo accounts can be used for one-click sign in.')
                ->danger()
                ->send();

            return null;
        }
        $this->form->fill(['email' => $email, 'password' => Demo::password()]);

        return $this->authenticate();
    }
}
