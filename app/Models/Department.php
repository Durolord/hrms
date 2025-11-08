<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
class Department extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $casts = [
        'status' => 'bool',
    ];
    protected $fillable = [
        'name',
        'status',
    ];
    public function designations()
    {
        return $this->hasMany(Designation::class);
    }
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function openings()
    {
        return $this->hasMany(Opening::class);
    }
    public function heads()
    {
        return $this->belongsToMany(Employee::class, 'department_heads')
            ->withPivot('branch_id')
            ->withTimestamps();
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}