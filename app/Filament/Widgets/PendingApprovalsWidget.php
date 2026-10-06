<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class PendingApprovalsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Waiting for you';

    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && count(static::visibleStats($user)) > 0;
    }

    /**
     * Pending leave requests this user can decide: everything for Admin/HR Manager, otherwise direct reports.
     */
    public static function approvableLeaves(User $user): Builder
    {
        $query = Leave::where('status', 'Pending');
        if ($user->hasAnyRole(['Admin', 'HR Manager'])) {
            return $query;
        }
        if (! $user->employee) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereHas('employee', fn (Builder $q) => $q->where('manager_id', $user->employee->id));
    }

    /**
     * @return array<string, array{value: int, icon: string, url: string}>
     */
    protected static function visibleStats(User $user): array
    {
        $stats = [];
        if ($user->can('approve_leave') || $user->can('reject_leave')) {
            $stats['Leave requests'] = [
                'value' => static::approvableLeaves($user)->count(),
                'icon' => 'heroicon-o-calendar-days',
                'url' => route('filament.admin.resources.leaves.index'),
            ];
        }
        if ($user->can('view_any_payroll')) {
            $stats['Payrolls to approve or pay'] = [
                'value' => Payroll::whereIn('status', ['Pending', 'Approved'])->count(),
                'icon' => 'heroicon-o-banknotes',
                'url' => route('filament.admin.resources.payrolls.index'),
            ];
        }
        if ($user->can('moveStage_applicant')) {
            $stats['Applicants to review'] = [
                'value' => Applicant::where('status', 'Applied')->count(),
                'icon' => 'heroicon-o-user-plus',
                'url' => route('filament.admin.resources.applicants.index'),
            ];
        }

        return $stats;
    }

    protected function getStats(): array
    {
        return collect(static::visibleStats(auth()->user()))
            ->map(fn (array $s, string $label) => Stat::make($label, $s['value'])
                ->icon($s['icon'])
                ->url($s['url'])
                ->color($s['value'] > 0 ? 'warning' : 'success'))
            ->values()
            ->all();
    }
}
