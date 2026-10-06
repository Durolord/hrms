<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends \Filament\Pages\Dashboard
{
    use InteractsWithFormActions;

    public function getHeading(): string|Htmlable
    {
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        return $greeting.', '.str(auth()->user()->name)->before(' ');
    }

    public function getSubheading(): ?string
    {
        return now()->format('l, j F Y');
    }

    /**
     * Quick links: [group label, icon, [[label, route name, permission]...]]
     */
    protected function quickLinks(): array
    {
        return [
            ['People', 'heroicon-m-user-group', [
                ['Employees', 'employees.index', 'view_any_employee'],
                ['New employee', 'employees.create', 'create_employee'],
                ['Attendance', 'attendances.index', 'view_any_attendance'],
                ['Leaves', 'leaves.index', 'view_any_leave'],
                ['Leave types', 'leave-types.index', 'view_any_leave::type'],
                ['Skills', 'skills.index', 'view_any_skill'],
            ]],
            ['Payroll', 'heroicon-m-banknotes', [
                ['Payrolls', 'payrolls.index', 'view_any_payroll'],
                ['Pay scales', 'pay-scales.index', 'view_any_pay::scale'],
                ['Allowances', 'allowances.index', 'view_any_allowance'],
                ['New allowance', 'allowances.create', 'create_allowance'],
                ['Deductions', 'deductions.index', 'view_any_deduction'],
                ['Bonuses', 'bonuses.index', 'view_any_bonus'],
            ]],
            ['Recruitment', 'heroicon-m-briefcase', [
                ['Openings', 'openings.index', 'view_any_opening'],
                ['New opening', 'openings.create', 'create_opening'],
            ]],
        ];
    }

    protected function getHeaderActions(): array
    {
        $groups = [];

        foreach ($this->quickLinks() as [$label, $icon, $links]) {
            $actions = collect($links)
                ->filter(fn (array $l): bool => (bool) auth()->user()?->can($l[2]))
                ->map(fn (array $l): Action => Action::make(str($l[1])->replace('.', '_')->toString())
                    ->label($l[0])
                    ->url(route('filament.admin.resources.'.$l[1])))
                ->values()
                ->all();

            if ($actions !== []) {
                $groups[] = ActionGroup::make($actions)
                    ->label($label)
                    ->icon($icon)
                    ->button()
                    ->color('gray');
            }
        }

        return $groups;
    }
}
