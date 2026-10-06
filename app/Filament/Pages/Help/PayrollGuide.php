<?php

namespace App\Filament\Pages\Help;

class PayrollGuide extends HelpPage
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payroll';

    protected static ?string $title = 'Payroll';

    protected static ?string $slug = 'help/payroll';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.help.payroll';
}
