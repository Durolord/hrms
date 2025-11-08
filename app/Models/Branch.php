<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $casts = [
        'status' => 'bool',
    ];
    protected $fillable = [
        'name',
        'address',
        'phone',
        'status',
    ];
    public function applicants()
    {
        return $this->hasMany(Applicant::class);
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