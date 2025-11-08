<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
class Designation extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $casts = [
        'status' => 'bool',
    ];
    protected $fillable = [
        'name',
        'pay_scale_id',
        'status',
    ];
    public function pay_scale()
    {
        return $this->belongsTo(PayScale::class);
    }
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function openings()
    {
        return $this->hasMany(Opening::class);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}