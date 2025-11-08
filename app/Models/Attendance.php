<?php
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
class Attendance extends Model
{
    use HasFactory;
    protected $casts = [
        'date' => 'datetime',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'employee_id' => 'int',
    ];
    protected $fillable = [
        'date',
        'time_in',
        'time_out',
        'break_start',
        'break_end',
        'employee_id',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($attendance) {
            $existingAttendance = $attendance->employee->attendances()
                ->where('date', $attendance->date)
                ->exists();
            if ($existingAttendance) {
                throw new \Exception('Duplicate attendance entry for the same date.');
            }
        });
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function totalWorkingHours(): float
    {
        if (! $this->time_in || ! $this->time_out) {
            return 0;
        }
        $start = Carbon::parse($this->time_in);
        $end = Carbon::parse($this->time_out);
        $breakStart = $this->break_start ? Carbon::parse($this->break_start) : null;
        $breakEnd = $this->break_end ? Carbon::parse($this->break_end) : null;
        $totalHours = $end->diffInHours($start);
        if ($breakStart && $breakEnd) {
            $breakHours = $breakEnd->diffInHours($breakStart);
            $totalHours -= $breakHours;
        }
        return $totalHours;
    }
    /**
     * Calculate total break time for this attendance record.
     *
     * @return int Total break time in minutes.
     */
    public function getTotalBreakTimeAttribute(): int
    {
        if (! $this->break_start || ! $this->break_end) {
            return 0;
        }
        return Carbon::parse($this->break_start)->diffInMinutes(Carbon::parse($this->break_end));
    }
    /**
     * Get total break time for a given month.
     *
     * @param  int|null  $month  Defaults to the current month.
     * @param  int|null  $year  Defaults to the current year.
     * @return int Total break time in minutes.
     */
    public function getMonthlyBreakTime(?int $month = null, ?int $year = null): int
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        return self::where('employee_id', $this->employee_id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->sum(fn ($attendance) => $attendance->total_break_time);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}