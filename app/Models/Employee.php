<?php
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
class Employee extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;
    protected $casts = [
        'employment_start_date' => 'datetime',
        'active' => 'bool',
        'user_id' => 'int',
        'department_id' => 'int',
        'designation_id' => 'int',
        'pay_scale_id' => 'int',
        'branch_id' => 'int',
        'manager_id' => 'int',
    ];
    protected $fillable = [
        'name',
        'email',
        'phone',
        'employment_start_date',
        'active',
        'user_id',
        'department_id',
        'designation_id',
        'bank_id',
        'account_number',
        'pay_scale_id',
        'branch_id',
        'manager_id',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function (Employee $employee) {
            $user = User::firstOrCreate(
                ['email' => $employee->email],
                [
                    'name' => $employee->name,
                    'password' => Hash::make('password'),
                ]
            );
            $employee->user_id = $user->id;
        });
        static::updated(function ($employee) {
            if ($employee->user) {
                $employee->user->update([
                    'name' => $employee->name,
                    'email' => $employee->email,
                ]);
            }
        });
    }
    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media && $media->mime_type === 'application/pdf') {
            $this->addMediaConversion('thumb')
                ->width(368)
                ->height(232)
                ->pdfPageNumber(2)
                ->nonQueued();
        }
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
    public function getCamelCaseNameAttribute(): string
    {
        return str_replace(' ', '', ucwords($this->name));
    }
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
    public function pay_scale()
    {
        return $this->belongsTo(PayScale::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }
    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
    public function employee_documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }
    public function skills()
    {
        return $this->belongsToMany(Skill::class)
            ->withPivot('id')
            ->withTimestamps();
    }
    public function employees()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
    public function performance_reviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }
    public function headedDepartments()
    {
        return $this->belongsToMany(Department::class, 'department_heads')
            ->withPivot('branch_id')
            ->withTimestamps();
    }
    public function departmentHead()
    {
        return $this->hasOneThrough(
            Employee::class,
            DepartmentHead::class,
            'branch_id',
            'id',
            'branch_id',
            'employee_id'
        )->where('department_heads.department_id', $this->department_id);
    }
    public function totalLeaveDaysTaken(?int $leaveTypeId = null, ?int $year = null): int
    {
        $year = $year ?? now()->year;
        $query = $this->leaves()
            ->where('status', 'Approved')
            ->whereYear('start_date', $year);
        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }
        return $query->get()->sum(fn ($leave) => Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1);
    }
    public static function addSalaryAdjustment(array $data, $id): void
    {
        $adjustmentData = [
            'employee_id' => $id,
            'amount' => $data['amount'],
            'month' => $data['month'],
            'is_percentage' => $data['is_percentage'],
            'reason' => $data['reason'],
        ];
        if ($data['type'] === 'bonus') {
            Bonus::create($adjustmentData);
        } else {
            Deduction::create($adjustmentData);
        }
    }
}