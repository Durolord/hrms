<?php
namespace App\Models;
use App\Notifications\UserNotification;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\LogOptions;
class Leave extends Model
{
    use HasFactory;
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'approved_on' => 'datetime',
        'deducted_from_payroll' => 'bool',
        'is_half_day' => 'bool',
        'employee_id' => 'int',
        'leave_type_id' => 'int',
        'approver_id' => 'int',
    ];
    protected $fillable = [
        'start_date',
        'end_date',
        'is_half_day',
        'approved_on',
        'status',
        'rejection_reason',
        'deducted_from_payroll',
        'employee_id',
        'leave_type_id',
        'approver_id',
    ];
    protected static function boot()
    {
        parent::boot();
        static::created(function ($leave) {
            if ($leave->employee?->manager?->user) {
                $leave->employee->manager->user->notify(new UserNotification(
                    title: 'New Leave Request',
                    message: "{$leave->employee->name} has requested leave.",
                    url: route('filament.admin.resources.leaves.view', $leave->id),
                    channels: ['filament', 'email']
                ));
            }
        });
        static::updating(function ($leave) {
            if ($leave->isDirty('status') && $leave->getOriginal('status') !== 'Pending') {
                throw ValidationException::withMessages([
                    'data.status' => "A {$leave->getOriginal('status')} leave request can no longer change status.",
                ]);
            }
        });
        static::updated(function ($leave) {
            if ($leave->wasChanged('status') && $leave->status === 'Approved') {
                $leave->createPayrollDeduction();
            }
        });
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }
    public function lineManager()
    {
        return $this->belongsTo(Employee::class, 'line_manager_id');
    }
    /**
     * Weekdays between the dates (inclusive), minus public holidays; a half day counts as 0.5.
     */
    public static function countWorkingDays(CarbonInterface $start, CarbonInterface $end, bool $halfDay = false): float
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->startOfDay();
        if ($end->lt($start)) {
            return 0;
        }
        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => $date->toDateString())
            ->all();
        $days = 0;
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if ($day->isWeekend() || in_array($day->toDateString(), $holidays, true)) {
                continue;
            }
            $days++;
        }

        return $halfDay && $days > 0 ? 0.5 : (float) $days;
    }
    public function workingDays(): float
    {
        return self::countWorkingDays($this->start_date, $this->end_date, (bool) $this->is_half_day);
    }
    public function approve(?User $by = null): void
    {
        $this->update([
            'status' => 'Approved',
            'approved_on' => now(),
            'approver_id' => $by?->id,
        ]);
        $this->notifyDecision('Approved', 'has been approved', $by);
    }
    public function reject(?User $by = null, ?string $reason = null): void
    {
        $this->update([
            'status' => 'Rejected',
            'approved_on' => now(),
            'approver_id' => $by?->id,
            'rejection_reason' => $reason,
        ]);
        $this->notifyDecision('Rejected', 'has been rejected', $by);
    }
    private function notifyDecision(string $outcome, string $verb, ?User $by): void
    {
        $this->loadMissing('employee.user', 'employee.manager.user', 'leave_type');
        $url = route('filament.admin.resources.leaves.view', $this->id);
        $period = "{$this->start_date->format('d M Y')} to {$this->end_date->format('d M Y')}";
        $type = strtolower($this->leave_type->name ?? 'leave');
        $reason = $this->rejection_reason ? " Reason: {$this->rejection_reason}" : '';
        $this->employee->user?->notify(new UserNotification(
            title: "Leave {$outcome}",
            message: "Your {$type} request from {$period} {$verb}.{$reason}",
            url: $url,
            channels: ['filament', 'email']
        ));
        $watchers = $by?->hasRole('Admin')
            ? User::role('Admin')->get()
            : collect([$this->employee->manager?->user])->filter();
        $watchers->reject(fn ($user) => $user->id === $this->employee->user_id)->each(
            fn ($user) => $user->notify(new UserNotification(
                title: "Leave {$outcome} for Subordinate",
                message: "The {$type} request from {$period} {$verb} for {$this->employee->name}.",
                url: $url,
                channels: ['filament', 'email']
            ))
        );
    }
    private function createPayrollDeduction(): void
    {
        if ($this->deducted_from_payroll) {
            return;
        }
        $totalDeductionAmount = ($this->leave_type->deduction_amount ?? 0) * $this->workingDays();
        if ($totalDeductionAmount <= 0) {
            return;
        }
        Deduction::create([
            'employee_id' => $this->employee_id,
            'amount' => $totalDeductionAmount,
            'month' => $this->start_date->copy()->startOfMonth(),
            'reason' => $this->leave_type->name.' Deduction ('.$this->workingDays().' days)',
            'is_percentage' => false,
        ]);
        $this->deducted_from_payroll = true;
        $this->saveQuietly();
    }
    /**
     * Remaining days for the requested leave type (approved and pending requests both count as used).
     */
    public function leaveBalance(?int $year = null): float
    {
        return $this->employee->leaveBalanceFor($this->leave_type, $year);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}
