<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\Payroll;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('addDocument')
                ->form([
                    FileUpload::make('documents')
                        ->multiple()
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ])
                        ->storeFileNamesIn('attachment_file_names')
                        ->moveFiles()
                        ->maxSize(20480)
                        ->label('Upload Documents')
                        ->helperText('Allowed file types: PDF and common image formats'),
                ])
                ->action(function (array $data): void {
                    if (! empty($data['documents'])) {
                        $attachmentFileNames = $data['attachment_file_names'] ?? [];
                        foreach ($data['documents'] as $index => $uploadedFile) {
                            $fileName = $attachmentFileNames[$index] ?? basename($uploadedFile);
                            $this->record->addMediaFromDisk($uploadedFile, 'public')
                                ->withCustomProperties([
                                    'name' => $fileName,
                                ])
                                ->toMediaCollection('documents');
                        }
                        Notification::make()
                            ->title('Documents uploaded successfully.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('No documents were uploaded.')
                            ->warning()
                            ->send();
                    }
                }),
            Actions\Action::make('add_bonus_or_deduction')
                ->label('Add Bonus / Deduction')
                ->icon('heroicon-o-currency-dollar')
                ->modal()
                ->modalHeading('Add Bonus or Deduction')
                ->form([
                    Select::make('type')
                        ->label('Adjustment Type')
                        ->options([
                            'bonus' => 'Bonus',
                            'deduction' => 'Deduction',
                        ])
                        ->default('bonus')
                        ->required(),
                    Toggle::make('is_percentage')
                        ->label('Is Percentage?')
                        ->default(false)
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('amount', 0)),
                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->live()
                        ->maxValue(fn (Get $get) => $get('is_percentage') ? 50 : null)
                        ->helperText(fn (Get $get) => $get('is_percentage') ? 'Max 50% allowed' : ''),
                    Flatpickr::make('month')
                        ->monthSelect()
                        ->maxDate(now()->startOfMonth())
                        ->required(),
                    Textarea::make('reason')
                        ->label('Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(fn (array $data) => $this->addSalaryAdjustment($data))
                ->successNotificationTitle('Salary adjustment added successfully'),
        ];
    }

    protected function addSalaryAdjustment(array $data): void
    {
        $employeeId = $this->record->id;
        $month = $data['month'];
        $type = $data['type'];
        $hasFinalizedPayroll = Payroll::where('employee_id', $employeeId)
            ->whereMonth('month', '=', \Carbon\Carbon::parse($month)->month)
            ->whereYear('month', '=', \Carbon\Carbon::parse($month)->year)
            ->where('status', '!=', 'Pending')
            ->exists();
        if ($hasFinalizedPayroll) {
            $message = $type === 'bonus'
                ? 'Bonuses cannot be added because the employee has a finalized payroll for the selected month.'
                : 'Deductions cannot be made because the employee has a finalized payroll for the selected month.';
            Notification::make()
                ->title('Adjustment cannot be created')
                ->body($message)
                ->danger()
                ->persistent()
                ->send();
        } else {
            $adjustmentData = [
                'employee_id' => $employeeId,
                'amount' => $data['amount'],
                'month' => $month,
                'is_percentage' => $data['is_percentage'],
                'reason' => $data['reason'],
            ];
            if ($type === 'bonus') {
                Bonus::create($adjustmentData);
            } else {
                Deduction::create($adjustmentData);
            }
            Notification::make()
                ->title('Salary adjustment added successfully')
                ->body($type === 'bonus' ? 'Bonus has been successfully added.' : 'Deduction has been successfully added.')
                ->success()
                ->send();
        }
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
