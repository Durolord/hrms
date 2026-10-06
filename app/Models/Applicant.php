<?php
namespace App\Models;
use App\Notifications\ApplicantUpdate;
use App\Notifications\UserNotification;
use App\Notifications\WelcomeNewHire;
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
        'interview_at',
        'interview_location',
        'job_status',
    ];
    protected $casts = [
        'interview_at' => 'datetime',
    ];
    public function opening()
    {
        return $this->belongsTo(Opening::class);
    }
    public function employee()
    {
        return $this->hasOne(Employee::class, 'email', 'email');
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
        $this->emailApplicant("Your application for {$this->opening->title}", [
            "Thank you for your interest in the {$this->opening->title} role and for the time you invested in applying.",
            'After careful consideration we have decided not to move forward with your application at this time. We wish you every success.',
        ]);
    }
    public function moveToNextStage()
    {
        $currentStageIndex = array_search($this->status, $this->stages);
        if ($currentStageIndex === false || ! isset($this->stages[$currentStageIndex + 1])) {
            return;
        }
        $next = $this->stages[$currentStageIndex + 1];
        if ($next === 'Hired') {
            $this->hire();

            return;
        }
        $this->update(['status' => $next]);
        if ($next === 'Shortlisted') {
            $this->emailApplicant("Your application for {$this->opening->title}", [
                "Good news: you have been shortlisted for the {$this->opening->title} role. We will be in touch with the next steps.",
            ]);
        }
    }
    public function scheduleInterview(\Carbon\CarbonInterface $at, ?string $location = null): void
    {
        $this->update(['interview_at' => $at, 'interview_location' => $location]);
        $this->emailApplicant("Interview invitation: {$this->opening->title}", array_filter([
            "We would like to invite you to an interview for the {$this->opening->title} role.",
            'When: '.$at->format('l, j F Y \a\t g:i A'),
            $location ? "Where: {$location}" : null,
            'Please reply to this email if you cannot make it.',
        ]));
    }
    public function sendConfirmation(): void
    {
        $this->emailApplicant("We received your application for {$this->opening->title}", [
            "Thank you for applying for the {$this->opening->title} role. Your application is now under review.",
            'We will contact you if your profile matches what we are looking for.',
        ]);
    }
    /**
     * @param  list<string>  $lines
     */
    protected function emailApplicant(string $subject, array $lines): void
    {
        try {
            \Illuminate\Support\Facades\Notification::route('mail', $this->email)
                ->notify(new ApplicantUpdate($subject, $this->name, array_values($lines)));
        } catch (\Throwable $e) {
            \Log::warning('Applicant email failed: '.$e->getMessage(), ['applicant_id' => $this->id]);
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
            $user = $employee->user;
            $token = \Illuminate\Support\Facades\Password::broker(config('filament.auth.passwords', null))->createToken($user);
            $user->notify(new WelcomeNewHire(
                \Filament\Facades\Filament::getPanel('admin')->getResetPasswordUrl($token, $user),
                $employee->designation->name ?? 'a member of staff'
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