<?php
class Allowance extends Model
{
    protected $fillable = [
        'pay_scale_id',
        'employee_id',
        'amount',
        'reason',
        'is_percentage',
    ];
    public function pay_scale()
    {
        return $this->belongsTo(PayScale::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function payrolls()
    {
        return $this->belongsToMany(Payroll::class, 'payroll_allowance')
            ->withPivot('id', 'amount')
            ->withTimestamps();
    }
    public function amount(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->is_percentage && $this->pay_scale) {
                    return $this->pay_scale->basic_salary * ($value / 100);
                }
                return $value;
            }
        );
    }
}
class Applicant extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'opening_id',
        'cv',
        'avatar',
        'status',
        'job_status',
    ];
    public function opening()
    {
        return $this->belongsTo(Opening::class);
    }
}
class Attendance extends Model
{
    protected $fillable = [
        'date',
        'time_in',
        'time_out',
        'break_start',
        'break_end',
        'employee_id',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
class AttendanceSummary extends Model
{
    protected $fillable = ['date', 'total_attendances'];
}
class Bank extends Model
{
    protected $fillable = ['name', 'code'];
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
class Bonus extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'month',
        'reason',
        'is_percentage',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function payrolls()
    {
        return $this->belongsToMany(Payroll::class, 'payroll_bonus')
            ->withPivot('id', 'amount')
            ->withTimestamps();
    }
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
}
class Branch extends Model
{
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
}
class Company extends Model
{
    protected $fillable = [
        'name',
    ];
}
class Deduction extends Model
{
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
}
class Department extends Model
{
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
}
class Designation extends Model
{
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
}
class Employee extends Model implements HasMedia
{
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
}
class Leave extends Model
{
    protected $fillable = [
        'start_date',
        'end_date',
        'approved_on',
        'status',
        'deducted_from_payroll',
        'employee_id',
        'leave_type_id',
        'approver_id',
    ];
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }
    public function lineManager()
    {
        return $this->belongsTo(Employee::class, 'line_manager_id');
    }
}
class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'deduction_amount',
        'is_percentage' => 'boolean',
        'max_days',
    ];
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}
class Opening extends Model
{
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
    public function qualifications()
    {
        return $this->belongsToMany(Qualification::class, 'opening_qualification');
    }
    public function responsibilities()
    {
        return $this->belongsToMany(Responsibility::class, 'opening_responsibility');
    }
}
class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'status',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function allowances()
    {
        return $this->hasMany(Allowance::class, 'pay_scale_id', 'pay');
    }
    public function current_allowances()
    {
        return $this->hasMany(PayrollAllowanceSnapshot::class);
    }
    public function current_deductions()
    {
        return $this->hasMany(PayrollDeductionSnapshot::class);
    }
    public function current_bonuses()
    {
        return $this->hasMany(PayrollBonusSnapshot::class);
    }
    public function getPayAttribute()
    {
        return $this->employee->designation->pay_scale_id ?? null;
    }
    public function deductions()
    {
        return $this->hasMany(Deduction::class, 'employee_id', 'employee_id')
            ->whereYear('deductions.month', $this->month->year)
            ->whereMonth('deductions.month', $this->month->month);
    }
    public function bonuses()
    {
        return $this->hasMany(Bonus::class, 'employee_id', 'employee_id')
            ->whereYear('bonuses.month', $this->month->year)
            ->whereMonth('bonuses.month', $this->month->month);
    }
}
class PayrollAllowanceSnapshot extends Model
{
    protected $fillable = [
        'payroll_id',
        'allowance_id',
        'pay_scale_id',
        'name',
        'amount',
    ];
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
class PayrollBonusSnapshot extends Model
{
    protected $fillable = [
        'payroll_id',
        'bonus_id',
        'name',
        'amount',
    ];
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }
}
class PayrollDeductionSnapshot extends Model
{
    protected $fillable = [
        'payroll_id',
        'deduction_id',
        'name',
        'amount',
    ];
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
    public function deduction()
    {
        return $this->belongsTo(Deduction::class);
    }
}
class PayScale extends Model
{
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
}
class Qualification extends Model
{
    protected $fillable = ['description'];
    public function openings()
    {
        return $this->belongsToMany(Opening::class, 'opening_qualification');
    }
}
class Responsibility extends Model
{
    protected $fillable = ['description'];
    public function openings()
    {
        return $this->belongsToMany(Opening::class, 'opening_responsibility');
    }
}
class Skill extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
    ];
    public function openings()
    {
        return $this->belongsToMany(Opening::class)
            ->withPivot('id')
            ->withTimestamps();
    }
}
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
    public function leaves()
    {
        return $this->hasMany(Leave::class, 'approver_id');
    }
}