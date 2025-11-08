<?php
namespace App\Models;
use App\Notifications\UserNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class Leave extends Model
{
    use HasFactory;
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'approved_on' => 'datetime',
        'deducted_from_payroll' => 'bool',
        'employee_id' => 'int',
        'leave_type_id' => 'int',
        'approver_id' => 'int',
    ];
    protected $fillable = [
        'start_date',
        'end_date',
        'approved_on',
        'status',
        'deducted_from_payroll',
        'employee_id',
        'leave_type_id',
        'approver_id',
    ];
    protected static function boot()
    {
        parent::boot();
        static::created(function ($leave) {
            if ($leave->employee->manager) {
                $leave->employee->manager->user->notify(new UserNotification(
                    title: 'New Leave Request',
                    message: "{$leave->employee->name} has requested leave.",
                    url: route('filament.admin.resources.leaves.view', $leave->id),
                    channels: ['filament']
                ));
            }
        });
        static::creating(function ($leave) {
            $overlappingLeave = $leave->employee?->leaves()
                ->where('status', 'Approved')
                ->where(function ($query) use ($leave) {
                    $query->whereBetween('start_date', [$leave->start_date, $leave->end_date])
                        ->orWhereBetween('end_date', [$leave->start_date, $leave->end_date])
                        ->orWhere(function ($query) use ($leave) {
                            $query->where('start_date', '<=', $leave->start_date)
                                ->where('end_date', '>=', $leave->end_date);
                        });
                })
                ->exists();
            if ($overlappingLeave) {
                $leave->employee->user->notify(new UserNotification(
                    title: 'Duplicate Leave',
                    message: 'You have already applied for leave during this period.',
                    channels: ['filament']
                ));
                return false;
            }
        });
        static::updated(function ($leave) {
            if ($leave->status === 'Approved') {
                $leave->employee->user->notify(new UserNotification(
                    title: 'Leave Approved',
                    message: 'Your leave request has been approved.',
                    url: route('filament.admin.resources.leave-types.view', $leave->id),
                    channels: ['filament']
                ));
            } elseif ($leave->status === 'Rejected') {
                $leave->employee->user->notify(new UserNotification(
                    title: 'Leave Rejected',
                    message: 'Your leave request has been rejected.',
                    url: route('filament.admin.resources.leave-types.view', $leave->id),
                    channels: ['filament']
                ));
            }
            if ($leave->status === 'Approved') {
                $baseDeductionAmount = $leave->leave_type->deduction_amount ?? 0;
                $leaveLength = $leave->start_date->diffInDays($leave->end_date) + 1;
                $totalDeductionAmount = $baseDeductionAmount * $leaveLength;
                if ($totalDeductionAmount > 0) {
                    Deduction::create([
                        'employee_id' => $leave->employee_id,
                        'amount' => $totalDeductionAmount,
                        'month' => $leave->start_date->startOfMonth(),
                        'reason' => $leave->leave_type->name.' Deduction ('.$leaveLength.' days)',
                        'is_percentage' => false,
                    ]);
                    $leave->deducted_from_payroll = true;
                    $leave->saveQuietly();
                }
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
    public function leaveBalance(?int $year = null): int
    {
        $year = $year ?? now()->year;
        $leaveType = $this->employee->leave_type;
        $leaveTypeId = $leaveType->id ?? null;
        $totalLeaveDays = $leaveType->max_days ?? 30;
        $usedLeaveDays = $this->employee->totalLeaveDaysTaken($leaveTypeId, $year);
        return max($totalLeaveDays - $usedLeaveDays, 0);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}