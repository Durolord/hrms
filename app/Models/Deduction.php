<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class Deduction extends Model
{
    use HasFactory;
    protected $casts = [
        'employee_id' => 'int',
        'amount' => 'float',
        'month' => 'datetime',
        'is_percentage' => 'boolean',
    ];
    protected $fillable = [
        'employee_id',
        'amount',
        'month',
        'reason',
        'is_percentage',
    ];
    public function amount(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->is_percentage) {
                    $employee = $this->employee;
                    if ($employee && $employee->designation && $employee->designation->pay_scale) {
                        return $employee->designation->pay_scale->basic_salary * ($value / 100);
                    }
                    return 0;
                }
                return $value;
            }
        );
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}