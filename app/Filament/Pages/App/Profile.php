<?php

namespace App\Filament\Pages\App;

use App\Filament\Actions\GeneratePasswordAction;
use App\Models\User;
use App\Support\Demo;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile;

class Profile extends EditProfile
{
    public function getBreadcrumbs(): array
    {
        return [
            null => __('Dashboard'),
            'profile' => __('Profile'),
        ];
    }

    public function form(Form $form): Form
    {
        /** @var TextInput $passwordComponent */
        $passwordComponent = $this->getPasswordFormComponent();
        /** @var TextInput $emailComponent */
        $emailComponent = $this->getEmailFormComponent();
        // Shared demo accounts keep their sign-in details for the next visitor (also enforced in DemoGuard).
        $user = $this->getUser();
        $locked = $user instanceof User && Demo::protects($user);
        $lockedHint = $locked ? 'Locked on shared demo accounts so the next visitor can sign in.' : null;

        return $form->schema([
            Section::make()
                ->inlineLabel(false)
                ->schema([
                    $this->getNameFormComponent(),
                    $emailComponent->disabled($locked)->helperText($lockedHint),
                    $passwordComponent->disabled($locked)->helperText($lockedHint)->suffixActions([
                        GeneratePasswordAction::make()->hidden($locked),
                    ]),
                    $this->getPasswordConfirmationFormComponent()->disabled($locked),
                ]),
            Section::make('Notifications')
                ->schema([
                    Toggle::make('email_notifications')
                        ->label('Email me about approvals, payslips and reminders')
                        ->helperText('In-app notifications are always delivered.'),
                ]),
        ]);
    }
}
