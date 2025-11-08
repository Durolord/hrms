<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Noxo\FilamentActivityLog\Pages\ListActivities;

class AuditLog extends ListActivities
{
    use HasPageShield;

    protected static ?string $navigationGroup = 'Admin';

    protected function getShieldRedirectPath(): string
    {
        return '/';
    }
}
