<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class Allowance extends Model
{
    use HasFactory;
    protected $casts = [
        'employee_id' => 'int',
        'amount' => 'float',
        'is_percentage' => 'boolean',
    ];
    protected $fillable = [
        'pay_scale_id',
        'employee_id',
        'amount',
        'reason',
        'is_percentage',
    ];
    public function pay_scale()
    {
        return $this->belongsTo(PayScale::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function payrolls()
    {
        return $this->belongsToMany(Payroll::class, 'payroll_allowance')
            ->withPivot('id', 'amount')
            ->withTimestamps();
    }
    public function amount(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->is_percentage && $this->pay_scale) {
                    return $this->pay_scale->basic_salary * ($value / 100);
                }
                return $value;
            }
        );
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}