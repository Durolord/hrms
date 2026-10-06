<?php
namespace App\Models;
use App\Notifications\PayslipReady;
use App\Notifications\UserNotification;
use App\Traits\HasSettingsAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class Payroll extends Model
{
    use HasFactory;
    use HasSettingsAttributes;
    protected $casts = [
        'employee_id' => 'int',
        'month' => 'datetime:Y-m',
        'basic_salary' => 'float',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'status',
        'approved_at',
        'paid_at',
    ];
    protected static function boot()
    {
        parent::boot();
        static::created(function ($payroll) {
            $payroll->employee->user->notify(new UserNotification(
                title: 'Payroll Generated',
                message: "Your payroll for {$payroll->month->format('F Y')} has been generated.",
                url: route('filament.admin.resources.payrolls.view', $payroll->id),
                channels: ['filament']
            ));
        });
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function allowances()
    {
        return $this->hasMany(Allowance::class, 'pay_scale_id', 'pay');
    }
    public function current_allowances()
    {
        return $this->hasMany(PayrollAllowanceSnapshot::class);
    }
    public function current_deductions()
    {
        return $this->hasMany(PayrollDeductionSnapshot::class);
    }
    public function current_bonuses()
    {
        return $this->hasMany(PayrollBonusSnapshot::class);
    }
    public function getPayAttribute()
    {
        return $this->employee->designation->pay_scale_id ?? null;
    }
    public function deductions()
    {
        return $this->hasMany(Deduction::class, 'employee_id', 'employee_id')
            ->whereYear('deductions.month', $this->month->year)
            ->whereMonth('deductions.month', $this->month->month);
    }
    public function bonuses()
    {
        return $this->hasMany(Bonus::class, 'employee_id', 'employee_id')
            ->whereYear('bonuses.month', $this->month->year)
            ->whereMonth('bonuses.month', $this->month->month);
    }
    public function getTotalBonusesAttribute(): float
    {
        return (float) ($this->isFrozen() ? $this->current_bonuses : $this->bonuses)->sum('amount');
    }
    public function getTotalAllowancesAttribute(): float
    {
        return (float) ($this->isFrozen() ? $this->current_allowances : $this->allowances)->sum('amount');
    }
    public function getTotalDeductionsAttribute(): float
    {
        return (float) ($this->isFrozen() ? $this->current_deductions : $this->deductions)->sum('amount');
    }
    public function getNetSalaryAttribute(): float
    {
        return $this->basic_salary + $this->total_allowances + $this->total_bonuses - $this->total_deductions;
    }
    public function getTotalEarningsAttribute(): float
    {
        return $this->basic_salary + $this->total_allowances + $this->total_bonuses;
    }
    /**
     * Once a payroll leaves Pending its figures come from the frozen snapshots, so later edits to pay scales,
     * allowances or deductions cannot change a payslip that was already approved or paid.
     */
    public function isFrozen(): bool
    {
        return $this->status !== 'Pending';
    }
    public function previousPayroll(): ?self
    {
        return self::where('employee_id', $this->employee_id)
            ->where('month', '<', $this->month->copy()->startOfMonth())
            ->orderByDesc('month')
            ->first();
    }
    public function markApproved(): void
    {
        $this->update(['status' => 'Approved', 'approved_at' => now()]);
        $this->employee->user?->notify(new PayslipReady($this));
    }
    public function markPaid(): void
    {
        $this->update(['status' => 'Paid', 'paid_at' => now()]);
    }
    public function markRejected(): void
    {
        $this->update(['status' => 'Rejected']);
    }

    public function totalDeductions(): float
    {
        return $this->total_deductions;
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}