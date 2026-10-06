<?php

namespace App\Filament\Pages\Help;

use App\Support\Demo;
use Filament\Pages\Page;

/**
 * Read-only documentation pages, grouped last in the navigation and visible to every signed-in user.
 */
abstract class HelpPage extends Page
{
    protected static ?string $navigationGroup = 'Help & Documentation';

    /**
     * The demo account to sign in as for a role, for the "Try it" walkthroughs.
     *
     * @return array{role: string, name: string, email: string, summary: string}|null
     */
    public function demoAccount(string $role): ?array
    {
        return Demo::enabled() ? Demo::accounts()->firstWhere('role', $role) : null;
    }

    public function isDemo(): bool
    {
        return Demo::enabled();
    }
}
