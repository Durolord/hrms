<?php
namespace App\Models;
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
    ];
    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'status',
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
        return $this->bonuses->sum('amount');
    }
    public function getTotalAllowancesAttribute(): float
    {
        return $this->allowances->sum('amount');
    }
    public function getTotalDeductionsAttribute(): float
    {
        return $this->deductions->sum('amount');
    }
    public function getNetSalaryAttribute(): float
    {
        return $this->basic_salary + $this->total_allowances + $this->total_bonuses - $this->total_deductions;
    }
    public function getTotalEarningsAttribute(): float
    {
        return $this->basic_salary + $this->total_allowances + $this->bonuses;
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