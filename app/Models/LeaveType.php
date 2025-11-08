<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class LeaveType extends Model
{
    use HasFactory;
    protected $table = 'leave_types';
    protected $fillable = [
        'name',
        'description',
        'deduction_amount',
        'is_percentage' => 'boolean',
        'max_days',
    ];
    protected $casts = [
        'deduction_amount' => 'float',
    ];
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
    public function calculatedDeductionAmount($salary)
    {
        return $this->is_percentage ? ($salary * ($this->deduction_amount / 100)) : $this->deduction_amount;
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}