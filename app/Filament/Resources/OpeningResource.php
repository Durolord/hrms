<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OpeningResource\Pages;
use App\Filament\Resources\OpeningResource\RelationManagers;
use App\Models\Opening;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OpeningResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Opening::class;

    protected static ?string $navigationGroup = 'Recruitment and Openings';

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('create_opening');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'close',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('')
                    ->schema([
                        Infolists\Components\Fieldset::make('Job Details')
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label('Title')
                                    ->size('lg')
                                    ->weight('bold'),
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->autosize()
                    ->columnSpanFull(),
                Forms\Components\Select::make('department_id')
                    ->relationship(name: 'department', titleAttribute: 'name')
                    ->required(),
                Forms\Components\Select::make('designation_id')
                    ->relationship(name: 'designation', titleAttribute: 'name')
                    ->required(),
                Forms\Components\Select::make('branch_id')
                    ->relationship(name: 'branch', titleAttribute: 'name')
                    ->required(),
                Forms\Components\Select::make('skills')
                    ->multiple()
                    ->relationship(titleAttribute: 'name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('description')
                            ->required(),
                    ])
                    ->preload(),
                Forms\Components\Select::make('qualifications')
                    ->multiple()
                    ->relationship(titleAttribute: 'description')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('description')
                            ->required(),
                    ])
                    ->preload(),
                Forms\Components\Select::make('responsibilities')
                    ->multiple()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('description')
                            ->required(),
                    ])
                    ->relationship(titleAttribute: 'description')
                    ->preload(),
                Forms\Components\Toggle::make('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('designation.name')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('skills.name')
                    ->sortable()
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (! auth()->user()->can('view_outside_branch_employee')) {
                    return $query->where('branch_id', auth()->user()->employee->branch->id);
                }
            })
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->relationship('designation', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Applicants', [
                RelationManagers\ApplicantsRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'jobs' => Pages\showOpening::route('/jobs/{record}'),
            'index' => Pages\ListOpenings::route('/'),
            'create' => Pages\CreateOpening::route('/create'),
            'view' => Pages\ViewOpening::route('/{record}'),
            'edit' => Pages\EditOpening::route('/{record}/edit'),
        ];
    }
}
