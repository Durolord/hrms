<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class WhosOutWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = "Who's out (next 14 days)";

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->employee;
    }

    public function table(Table $table): Table
    {
        $department = auth()->user()->employee->department_id;

        return $table
            ->query(
                Leave::query()
                    ->where('status', 'Approved')
                    ->whereDate('start_date', '<=', now()->addDays(14))
                    ->whereDate('end_date', '>=', now()->startOfDay())
                    ->whereHas('employee', fn (Builder $q) => $q->where('department_id', $department))
                    ->with('employee')
            )
            ->defaultSort('start_date')
            ->paginated(false)
            ->emptyStateHeading('Nobody in your department is away')
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')->label('Employee'),
                Tables\Columns\TextColumn::make('start_date')->date('j M')->label('From'),
                Tables\Columns\TextColumn::make('end_date')->date('j M')->label('To'),
                Tables\Columns\TextColumn::make('status_today')
                    ->label('')
                    ->badge()
                    ->state(fn (Leave $record) => $record->start_date->lte(now()) ? 'Out today' : 'Upcoming')
                    ->color(fn (string $state) => $state === 'Out today' ? 'warning' : 'gray'),
            ]);
    }
}
