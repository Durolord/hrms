<?php
namespace App\Models;
use App\Notifications\UserNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\InteractsWithMedia;
use Symfony\Component\HttpFoundation\Response;
class Applicant extends Model
{
    use HasFactory;
    use InteractsWithMedia;
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
    protected array $stages = [
        'Applied',
        'Interviewed',
        'Shortlisted',
        'Hired',
    ];
    public function reject()
    {
        $this->update(['status' => 'Rejected']);
    }
    public function moveToNextStage()
    {
        $currentStageIndex = array_search($this->status, $this->stages);
        if ($currentStageIndex !== false && isset($this->stages[$currentStageIndex + 1])) {
            $this->update(['status' => $this->stages[$currentStageIndex + 1]]);
        }
    }
    public function downloadCv(): Response
    {
        if (! Storage::exists($this->cv)) {
            $publicPath = url("storage/{$this->cv}");
            return response("CV not found. Expected URL: {$publicPath}", 404);
        }
        $fileName = "{$this->name}'s CV.pdf";
        return response()->streamDownload(function () {
            echo Storage::get($this->cv);
        }, $fileName);
    }
    public function hire()
    {
        try {
            DB::transaction(function () {
                if ($this->status === 'Hired') {
                    throw new \Exception('Applicant already hired');
                }
                if (User::where('email', $this->email)->exists()) {
                    throw new \Exception('User with this email already exists');
                }
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make(Str::password(12)),
                ]);
                $employee = Employee::updateOrCreate(
                    ['email' => $this->email],
                    [
                        'user_id' => $user->id,
                        'name' => $this->name,
                        'phone' => $this->phone,
                        'department_id' => $this->opening->department_id,
                        'designation_id' => $this->opening->designation_id,
                        'branch_id' => $this->opening->branch_id,
                        'employment_start_date' => now(),
                    ]);
                $this->update(['status' => 'Hired']);
                $user->assignRole('Employee');
            });
            $this->sendHireNotifications();
        } catch (\Exception $e) {
            \Log::error('Hiring failed: '.$e->getMessage());
            throw $e;
        }
    }
    protected function sendHireNotifications()
    {
        try {
            $employee = $this->fresh()->employee;
            $employee->user->notify(new UserNotification(
                title: 'Congratulations!',
                message: 'You have been hired. Welcome to the team!',
                url: route('dashboard'),
                channels: ['email', 'filament']
            ));
            User::role('HR Manager')->each(function ($user) use ($employee) {
                $user->notify(new UserNotification(
                    title: 'New Hire Notification',
                    message: "{$employee->name} has been hired as {$employee->designation->name}.",
                    url: route('filament.admin.resources.employees.view', $employee->id),
                    channels: ['filament']
                ));
            });
        } catch (\Exception $e) {
            \Log::error('Notification failed: '.$e->getMessage());
        }
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }
}