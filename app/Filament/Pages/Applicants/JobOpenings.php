<?php

namespace App\Filament\Pages\Applicants;

use App\Models\Opening;
use App\Tables\Columns\JobCard;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
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
            ->query(
                Opening::query()
                    ->where('active', true)
                    ->with(['branch', 'department', 'skills'])
                    ->latest()
            )
            ->columns([
                Stack::make([
                    JobCard::make('id'),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->paginated([9, 18, 36])
            ->defaultPaginationPageOption(9)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->emptyStateHeading('No open positions match your search')
            ->emptyStateDescription('Try clearing the filters, or check back soon for new roles.')
            ->emptyStateIcon('heroicon-o-briefcase')
            ->filters([
                Filter::make('search')
                    ->form([
                        TextInput::make('q')
                            ->label('Search')
                            ->placeholder('Job title or keyword')
                            ->prefixIcon('heroicon-m-magnifying-glass'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['q'] ?? null,
                        fn (Builder $q, string $term) => $q->where(
                            fn (Builder $w) => $w
                                ->where('title', 'like', "%{$term}%")
                                ->orWhere('description', 'like', "%{$term}%")
                        )
                    )),
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
