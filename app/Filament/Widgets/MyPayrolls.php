<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\CollapsibleTableWidget as BaseWidget;
use App\Models\Payroll;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyPayrolls extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $employee = Auth::user()->employee->id;
        if (! $employee) {
            return Payroll::query()->whereRaw('0 = 1');
        }

        return Payroll::query()->where('employee_id', $employee);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('employee.name')
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('month')
                ->date('F Y')
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('basic_salary')
                ->numeric()
                ->sortable()
                ->color('slate')
                ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                ->toggleable(),
            Tables\Columns\TextColumn::make('total_allowances')
                ->numeric()
                ->sortable()
                ->color('emerald')
                ->formatStateUsing(fn ($state) => $state ? '+ ₦ '.number_format($state, 2) : '₦ 0.00')
                ->toggleable(),
            Tables\Columns\TextColumn::make('total_deductions')
                ->numeric()
                ->sortable()
                ->color('rose')
                ->formatStateUsing(fn ($state) => $state ? '- ₦ '.number_format($state, 2) : '₦ 0.00')
                ->toggleable(),
            Tables\Columns\TextColumn::make('net_salary')
                ->numeric()
                ->sortable()
                ->formatStateUsing(fn ($state) => $state ? '₦ '.number_format($state, 2) : '₦ 0.00')
                ->toggleable(),
            Tables\Columns\TextColumn::make('status')
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('payroll_month')
                ->form([
                    Flatpickr::make('month')
                        ->required()
                        ->maxDate(now()->format('Y-m'))
                        ->monthSelect(),
                ])
                ->query(fn (Builder $query, array $data): Builder => $query->when(
                    $data['month'],
                    fn (Builder $query, $date): Builder => $query
                        ->whereMonth('month', \Carbon\Carbon::parse($date)->month)
                        ->whereYear('month', \Carbon\Carbon::parse($date)->year)
                )
                ),
            Tables\Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Paid' => 'Paid',
                ])
                ->query(fn (Builder $query, array $data) => $data['value'] ? $query->where('status', $data['value']) : $query
                ),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('downloadPDF')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn ($record) => route('payroll.download-pdf', $record))
                ->openUrlInNewTab()
                ->visible(fn (Payroll $record) => $record->status != 'Pending'),
        ];
    }
}
