<?php

namespace App\Filament\Resources\SkillResource\Pages;

use App\Filament\Resources\SkillResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateSkill extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = SkillResource::class;
}
