<?php

namespace App\Console\Commands;

use App\Models\Leave;
use App\Models\Payroll;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Console\Command;

class SendApprovalReminders extends Command
{
    protected $signature = 'hr:send-reminders {--days=2 : Only remind about items waiting at least this many days}';

    protected $description = 'Remind managers of leave requests, and finance of payrolls, that are waiting for a decision';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));
        $sent = 0;

        // One digest per manager (or per HR Manager for employees without a manager).
        Leave::where('status', 'Pending')
            ->where('created_at', '<=', $cutoff)
            ->with('employee.manager.user')
            ->get()
            ->groupBy(fn (Leave $leave) => $leave->employee->manager?->user?->id ?? 'hr')
            ->each(function ($leaves, $key) use (&$sent) {
                $recipients = $key === 'hr' ? User::role('HR Manager')->get() : User::whereKey($key)->get();
                foreach ($recipients as $user) {
                    $user->notify(new UserNotification(
                        title: 'Leave requests awaiting your decision',
                        message: "{$leaves->count()} leave request(s) have been waiting for {$this->option('days')}+ days.",
                        url: route('filament.admin.resources.leaves.index'),
                        channels: ['filament', 'email']
                    ));
                    $sent++;
                }
            });

        // Draft payrolls older than the cutoff, and approved payrolls not yet paid.
        $pendingPayrolls = Payroll::whereIn('status', ['Pending', 'Approved'])->where('updated_at', '<=', $cutoff)->count();
        if ($pendingPayrolls > 0) {
            User::role(['Finance Manager', 'Admin'])->get()->each(function (User $user) use ($pendingPayrolls, &$sent) {
                $user->notify(new UserNotification(
                    title: 'Payrolls awaiting approval or payment',
                    message: "{$pendingPayrolls} payroll(s) are still Pending or Approved.",
                    url: route('filament.admin.resources.payrolls.index'),
                    channels: ['filament', 'email']
                ));
                $sent++;
            });
        }

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
