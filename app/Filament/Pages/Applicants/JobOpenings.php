<?php

namespace App\Filament\Pages\Applicants;

use App\Models\Opening;
use App\Tables\Columns\JobCard;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JobOpenings extends Page implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.applicants.job-openings';

    protected static string $layout = 'layouts.simple';

    protected static bool $shouldRegisterNavigation = false;

    public function getDefaultLayoutView(): string
    {
        return 'grid';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Opening::query()->where('active', true)->latest())
            ->columns([
                Stack::make([
                    JobCard::make('id'),
                ]),
            ])
            ->contentGrid([
                'lg' => 2,
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->filters([
                SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }

    protected function getTableActions(): array
    {
        return [
        ];
    }
}
