<?php

namespace App\Filament\Pages\App;

use App\Filament\Actions\GeneratePasswordAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
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

        return $form->schema([
            Section::make()
                ->inlineLabel(false)
                ->schema([
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent()->disabled(config('app.demo')),
                    $passwordComponent->disabled(config('app.demo'))->suffixActions([
                        GeneratePasswordAction::make(),
                    ]),
                    $this->getPasswordConfirmationFormComponent()->disabled(config('app.demo')),
                ]),
        ]);
    }
}
