<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Concerns\InteractsWithFormActions;

class Dashboard extends \Filament\Pages\Dashboard
{
    use InteractsWithFormActions;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('overview')
                    ->label('Attendances Overview')
                    ->url(route('filament.admin.resources.attendances.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_attendance')),
            ])
                ->icon('heroicon-m-clock')
                ->tooltip('Attendance'),
            ActionGroup::make([
                Action::make('allowances')
                    ->label('Allowances')
                    ->url(route('filament.admin.resources.allowances.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_allowance')),
                Action::make('create_allowance')
                    ->label('Create Allowance')
                    ->url(route('filament.admin.resources.allowances.create'))
                    ->visible(fn (): bool => auth()->user()->can('create_allowance')),
            ])
                ->icon('heroicon-m-currency-dollar')
                ->tooltip('Allowances'),
            ActionGroup::make([
                Action::make('payrolls')
                    ->label('Payrolls')
                    ->url(route('filament.admin.resources.payrolls.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_payroll')),
            ])
                ->icon('heroicon-m-receipt-percent')
                ->tooltip('Payroll'),
            ActionGroup::make([
                Action::make('employees')
                    ->label('Employees')
                    ->url(route('filament.admin.resources.employees.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_employee')),
                Action::make('createEmployees')
                    ->label('Create Employees')
                    ->url(route('filament.admin.resources.employees.create'))
                    ->visible(fn (): bool => auth()->user()->can('create_employee')),
            ])
                ->icon('heroicon-m-user')
                ->tooltip('Employees'),
            ActionGroup::make([
                Action::make('deductions')
                    ->label('Deductions')
                    ->url(route('filament.admin.resources.deductions.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_deduction')),
                Action::make('createDeductions')
                    ->label('Create Deductions')
                    ->url(route('filament.admin.resources.deductions.create'))
                    ->visible(fn (): bool => auth()->user()->can('create_deduction')),
            ])
                ->icon('heroicon-m-minus-circle')
                ->tooltip('Deductions'),
            ActionGroup::make([
                Action::make('bonuses')
                    ->label('Bonuses')
                    ->url(route('filament.admin.resources.bonuses.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_bonus')),
                Action::make('createBonuses')
                    ->label('Create Bonuses')
                    ->url(route('filament.admin.resources.bonuses.create'))
                    ->visible(fn (): bool => auth()->user()->can('create_bonus')),
            ])
                ->icon('heroicon-m-gift')
                ->tooltip('Bonuses'),
            ActionGroup::make([
                Action::make('leaveTypes')
                    ->label('Leave Types')
                    ->url(route('filament.admin.resources.leave-types.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_leave::type')),
                Action::make('createLeaveTypes')
                    ->label('Create Leave Type')
                    ->url(route('filament.admin.resources.leave-types.create'))
                    ->visible(fn (): bool => auth()->user()->can('create_leave::type')),
                Action::make('leaves')
                    ->label('Leaves')
                    ->url(route('filament.admin.resources.leaves.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_leave')),
            ])
                ->icon('heroicon-m-calendar')
                ->tooltip('Leaves'),
            ActionGroup::make([
                Action::make('openings')
                    ->label('Openings')
                    ->url(route('filament.admin.resources.openings.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_opening')),
                Action::make('createOpenings')
                    ->label('Create Openings')
                    ->url(route('filament.admin.resources.openings.create'))
                    ->visible(fn (): bool => auth()->user()->can('create_opening')),
            ])
                ->icon('heroicon-m-building-office')
                ->tooltip('Openings'),
            ActionGroup::make([
                Action::make('payScales')
                    ->label('Pay Scales')
                    ->url(route('filament.admin.resources.pay-scales.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_pay::scale')),
                Action::make('createPayScales')
                    ->label('Create Pay Scales')
                    ->url(route('filament.admin.resources.pay-scales.create'))
                    ->visible(fn (): bool => auth()->user()->can('create_pay::scale')),
            ])
                ->icon('heroicon-m-scale')
                ->tooltip('Pay Scales'),
            ActionGroup::make([
                Action::make('skills')
                    ->label('Skills')
                    ->url(route('filament.admin.resources.skills.index'))
                    ->visible(fn (): bool => auth()->user()->can('view_any_skill')),
                Action::make('createSkills')
                    ->label('Create Skills')
                    ->url(route('filament.admin.resources.skills.create'))
                    ->visible(fn (): bool => auth()->user()->can('create_skill')),
            ])
                ->icon('heroicon-m-adjustments-horizontal')
                ->tooltip('Skills'),
        ];
    }
}
