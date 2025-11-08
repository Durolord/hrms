<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Actions\GeneratePayrollsAction;
use App\Filament\Resources\PayrollResource;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            GeneratePayrollsAction::make('Generate'),
        ];
    }
}
