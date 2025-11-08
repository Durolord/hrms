<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateBranch extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = BranchResource::class;
}
