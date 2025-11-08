<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view_any_attendance');
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'import',
            'export',
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'recordCheckIn',
            'recordCheckOut',
            'recordBreak',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => auth()->user()->can('view_outside_branch_employee')
                            ? $query
                            : $query->where('branch_id', auth()->user()->employee->branch->id),
                    )
                    ->searchable(['name', 'email'])
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->native(false)
                    ->closeOnDateSelection()
                    ->required(),
                Forms\Components\TimePicker::make('time_in')
                    ->label('Time In')
                    ->displayFormat('h:i A')
                    ->seconds(false)
                    ->required(),
                Forms\Components\TimePicker::make('time_out')
                    ->label('Time Out')
                    ->seconds(false),
                Forms\Components\TimePicker::make('break_start')
                    ->seconds(false),
                Forms\Components\TimePicker::make('break_end')
                    ->seconds(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Flatpickr::make('day')
                            ->enableTime(false)
                            ->dateFormat('Y-m-d')
                            ->default(today())
                            ->maxDate(today()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['day'] ?? null,
                            fn (Builder $query, $day): Builder => $query->whereDate('date', '=', Carbon::parse($day))
                        );
                    }),
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AttendanceResource\Widgets\AttendanceCalendarWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'overview' => Pages\ListAttendances::route('/overview'),
            'index' => Pages\AttendanceCalendar::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
