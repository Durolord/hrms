<?php

namespace App\Filament\Resources\OpeningResource\Pages;

use App\Filament\Resources\OpeningResource;
use App\Models\Applicant;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class ShowOpening extends Page implements HasForms, HasInfolists
{
    use InteractsWithForms;
    use InteractsWithInfolists;
    use InteractsWithRecord;

    protected static string $resource = OpeningResource::class;

    protected static string $view = 'filament.resources.opening-resource.pages.view-opening';

    protected static string $layout = 'layouts.simple';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return $this->record->title ?? 'Job Opening';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record(self::getRecord())
            ->schema([
                Infolists\Components\Section::make('')
                    ->schema([
                        Infolists\Components\Fieldset::make('Job Details')
                            ->schema([
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Description')
                                    ->html()
                                    ->columnSpanFull(),
                            ]),
                        Infolists\Components\Fieldset::make('Job Information')
                            ->schema([
                                Infolists\Components\TextEntry::make('department.name')
                                    ->label('Department')
                                    ->weight('medium'),
                                Infolists\Components\TextEntry::make('designation.name')
                                    ->label('Designation')
                                    ->placeholder('N/A'),
                                Infolists\Components\TextEntry::make('branch.name')
                                    ->label('Branch')
                                    ->placeholder('N/A'),
                            ])
                            ->columns(3),
                        Infolists\Components\Fieldset::make('')
                            ->hidden(fn ($record) => $record->skills->isEmpty() &&
                                $record->qualifications->isEmpty() &&
                                $record->responsibilities->isEmpty()
                            )
                            ->schema([
                                Infolists\Components\TextEntry::make('skills.name')
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->label('Skills')
                                    ->columns(1)
                                    ->hidden(fn ($record) => $record->qualifications->isEmpty()),
                                Infolists\Components\TextEntry::make('qualifications.description')
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->label('Qualifications')
                                    ->columns(1)
                                    ->hidden(fn ($record) => $record->qualifications->isEmpty()),
                                Infolists\Components\TextEntry::make('responsibilities.description')
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->label('Responsibilities')
                                    ->columns(1)
                                    ->hidden(fn ($record) => $record->responsibilities->isEmpty()),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }

    public function getActions(): array
    {
        return [
            Action::make('apply')
                ->label('Apply Now')
                ->modal()
                ->color('cyan')
                ->modalHeading('Apply for '.$this->record->title)
                ->form([
                    TextInput::make('name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->required()
                        ->maxLength(20),
                    FileUpload::make('avatar')
                        ->avatar()
                        ->directory('avatars')
                        ->image()
                        ->imageEditor()
                        ->maxSize(1024)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->required(),
                    FileUpload::make('cv')
                        ->directory('cvs')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required(),
                    Select::make('job_status')
                        ->label('Job Status')
                        ->options([
                            'Employed' => 'Employed',
                            'Unemployed' => 'Unemployed',
                        ])
                        ->default('Unemployed')
                        ->required(),
                ])
                ->action(fn (array $data) => $this->submitApplication($data)),
        ];
    }

    protected function submitApplication(array $data): void
    {
        $exists = Applicant::where('email', $data['email'])
            ->where('opening_id', $this->record->id)
            ->exists();
        if ($exists) {
            Notification::make()
                ->title('You have already applied')
                ->body('Your application is already under review. Please be patient.')
                ->info()
                ->send();

            return;
        }
        Applicant::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'cv' => $data['cv'],
            'avatar' => $data['avatar'],
            'job_status' => $data['job_status'],
            'opening_id' => $this->record->id,
        ]);
        Notification::make()
            ->title('Application Submitted')
            ->body('Thank you for applying! Our team will review your application.')
            ->success()
            ->send();
    }
}
