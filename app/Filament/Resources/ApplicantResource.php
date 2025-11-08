<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicantResource\Pages;
use App\Models\Applicant;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicantResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Applicant::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Recruitment and Openings';

    public static function getPermissionPrefixes(): array
    {
        return [
            'shortlist',
            'moveStage',
            'viewAny',
            'create',
            'update',
            'delete',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('opening_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('job_status')
                    ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('email'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Applied' => 'primary',
                        'interviewed' => 'warning',
                        'Hired' => 'success',
                        'Rejected' => 'danger',
                    }),
                Infolists\Components\TextEntry::make('opening.title'),
                Infolists\Components\TextEntry::make('applied_at')->date(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('opening.title')
                    ->toggleable()
                    ->label('Opening'),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('job_status')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('job_status')
                    ->label('Job Status')
                    ->options([
                        'Employed' => 'Employed',
                        'Unemployed' => 'Unemployed',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->label('Applied Date')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download_cv')
                    ->label('Download CV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Applicant $record) => route('applicant.download-cv', $record))
                    ->openUrlInNewTab()
                    ->hidden(fn (Applicant $record) => empty($record->cv)),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('next_stage')
                        ->label('Next Stage')
                        ->color('primary')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->action(function ($record) {
                            $record->moveToNextStage();
                        })
                        ->visible(fn ($record): bool => auth()->user()->can('moveStage_applicant') && in_array($record->status, ['Applied', 'Interviewed', 'Shortlisted'])
                        ),
                    Tables\Actions\Action::make('reject')
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($record) {
                            $record->reject();
                        })
                        ->visible(fn ($record): bool => in_array($record->status, ['Applied', 'Interviewed', 'Shortlisted'])
                        ),
                    Tables\Actions\Action::make('hire')
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->modalHeading('Hire Applicant')
                        ->modalDescription('Are you sure you want to hire this applicant directly')
                        ->action(function ($record) {
                            $record->hire();
                        })
                        ->visible(fn ($record): bool => auth()->user()->can('shortlist_applicant') && $record->status == 'Applied'
                        ),
                ])
                    ->label('Candidate Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplicants::route('/'),
            'view' => Pages\ViewApplicant::route('/{record}'),
        ];
    }
}
