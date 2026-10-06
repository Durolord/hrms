<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class AnniversariesWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Work anniversaries this month';

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view_any_employee');
    }

    public function table(Table $table): Table
    {
        $month = now()->month;

        return $table
            ->query(
                Employee::query()
                    ->where('active', true)
                    ->whereMonth('employment_start_date', $month)
                    ->whereYear('employment_start_date', '<', now()->year)
                    // MySQL and SQLite both support ordering by the day-of-month through a raw expression.
                    ->orderByRaw(config('database.default') === 'sqlite'
                        ? "CAST(strftime('%d', employment_start_date) AS INTEGER)"
                        : 'DAY(employment_start_date)')
            )
            ->paginated(false)
            ->emptyStateHeading('No anniversaries this month')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('employment_start_date')->label('Date')->date('j M'),
                Tables\Columns\TextColumn::make('years')
                    ->label('Years')
                    ->state(fn (Employee $record) => now()->year - $record->employment_start_date->year),
            ]);
    }
}
