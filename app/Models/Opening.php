<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class Opening extends Model
{
    use HasFactory;
    protected $casts = [
        'department_id' => 'int',
        'designation_id' => 'int',
        'branch_id' => 'int',
        'active' => 'bool',
    ];
    protected $fillable = [
        'title',
        'description',
        'department_id',
        'designation_id',
        'branch_id',
        'active',
    ];
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }
    public function skills()
    {
        return $this->belongsToMany(Skill::class)
            ->withPivot('id')
            ->withTimestamps();
    }
    public function isActive(): bool
    {
        return $this->active && $this->applicants()->count() < $this->max_applicants;
    }
    public function applicantCount(): int
    {
        return $this->applicants()->count();
    }
    public function qualifications()
    {
        return $this->belongsToMany(Qualification::class, 'opening_qualification');
    }
    public function responsibilities()
    {
        return $this->belongsToMany(Responsibility::class, 'opening_responsibility');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}