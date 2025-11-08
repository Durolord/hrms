<?php

namespace App\Filament\Pages\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    use HasCustomLayout;

    protected static string $view = 'pages.auth.login';

    protected function getApplyActions(): array
    {
        return [
            Action::make('applyJob')
                ->label('Apply for Job')
                ->color('teal')
                ->url(route('jobs.show'))
                ->openUrlInNewTab(),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
