<?php

namespace App\Filament\Pages\Help;

class RecruitmentGuide extends HelpPage
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Recruitment';

    protected static ?string $title = 'Recruitment';

    protected static ?string $slug = 'help/recruitment';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.help.recruitment';
}
