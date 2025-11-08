<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class PayScale extends Model
{
    use HasFactory;
    protected $casts = [
        'basic_salary' => 'float',
        'active' => 'bool',
    ];
    protected $fillable = [
        'name',
        'active',
    ];
    public function allowances()
    {
        return $this->hasMany(Allowance::class);
    }
    public function designations()
    {
        return $this->hasMany(Designation::class);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}