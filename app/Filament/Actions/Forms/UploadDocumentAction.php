<?php

namespace App\Filament\Actions\Forms;

use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class UploadDocumentAction extends Action
{
    public Employee $employee;

    public static function getDefaultName(): ?string
    {
        return 'uploadDocument';
    }

    public $documents = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->employee = Auth::user()->employee;
        $this
            ->label('Add Document')
            ->form([
                SpatieMediaLibraryFileUpload::make('documents')
                    ->multiple()
                    ->collection('personal')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                    ])
                    ->downloadable()
                    ->label('Upload Documents')
                    ->helperText('Allowed file types: PDF and common image formats'),
            ])
            ->model($this->employee)
            ->action(function (array $data): void {
                if (! $this->employee) {
                    Notification::make()
                        ->title('Employee not found')
                        ->danger()
                        ->send();

                    return;
                }
                if (isset($data['documents']) && count($data['documents'])) {
                    foreach ($data['documents'] as $document) {
                        $this->employee->addMedia($document)
                            ->toMediaCollection('personal');
                    }
                    Notification::make()
                        ->title('Documents uploaded successfully!')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('No documents were uploaded.')
                        ->warning()
                        ->send();
                }
            });
    }
}
