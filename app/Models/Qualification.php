<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class Qualification extends Model
{
    use HasFactory;
    protected $fillable = ['description'];
    public function openings()
    {
        return $this->belongsToMany(Opening::class, 'opening_qualification');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}