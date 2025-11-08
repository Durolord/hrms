<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DailyAttendance extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->toggleable()
                    ->date(),
                Tables\Columns\TextColumn::make('time_in')
                    ->toggleable()
                    ->dateTime('h:i A'),
                Tables\Columns\TextColumn::make('time_out')
                    ->toggleable()
                    ->dateTime('h:i A'),
                Tables\Columns\TextColumn::make('totalBreakTime')
                    ->suffix(' minutes'),
            ]);
    }
}
