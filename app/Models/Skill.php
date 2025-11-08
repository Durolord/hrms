<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class Skill extends Model
{
    use HasFactory;
    protected $table = 'skills';
    protected $casts = [
        'status' => 'bool',
    ];
    protected $fillable = [
        'name',
        'description',
        'status',
    ];
    public function employees()
    {
        return $this->belongsToMany(Employee::class)
            ->withPivot('id')
            ->withTimestamps();
    }
    public function isRequiredForOpening($openingId): bool
    {
        return $this->openings()->where('opening_id', $openingId)->exists();
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}