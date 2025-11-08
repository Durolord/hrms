<?php

namespace App\Filament\Resources\DeductionResource\Pages;

use App\Filament\Resources\DeductionResource;
use App\Models\Payroll;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Noxo\FilamentActivityLog\Extensions\LogCreateRecord;

class CreateDeduction extends CreateRecord
{
    use LogCreateRecord;

    protected static string $resource = DeductionResource::class;

    protected function beforeCreate(array $data): void
    {
        $employeeId = $data['employee_id'];
        $month = $data['month'];
        $hasFinalizedPayroll = Payroll::where('employee_id', $employeeId)
            ->whereMonth('month', '=', \Carbon\Carbon::parse($month)->month)
            ->whereYear('month', '=', \Carbon\Carbon::parse($month)->year)
            ->where('status', '!=', 'Pending')
            ->exists();
        if ($hasFinalizedPayroll) {
            Notification::make()
                ->title('Deduction cannot be created')
                ->body('This beduction cannot be made because the employee has a finalized payroll for the selected month.')
                ->danger()
                ->persistent()
                ->send();
            $this->halt();
        }
    }
}
