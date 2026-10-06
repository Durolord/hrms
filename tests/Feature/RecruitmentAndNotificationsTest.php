<?php

use App\Models\Applicant;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Opening;
use App\Models\User;
use App\Notifications\ApplicantUpdate;
use App\Notifications\UserNotification;
use App\Notifications\WelcomeNewHire;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

function makeApplicant(string $email = 'candidate@example.test'): Applicant
{
    $template = makeEmployee();

    return Model::unguarded(function () use ($template, $email) {
        $opening = Opening::create([
            'title' => 'Accountant',
            'department_id' => $template->department_id,
            'designation_id' => $template->designation_id,
            'branch_id' => $template->branch_id,
        ]);

        return Applicant::create([
            'name' => 'Ada Candidate',
            'email' => $email,
            'phone' => '08000000000',
            'opening_id' => $opening->id,
        ])->fresh();
    });
}

describe('recruitment', function () {
    it('emails the applicant a confirmation, a shortlist notice, an interview invite and a rejection', function () {
        Notification::fake();
        $applicant = makeApplicant();

        $applicant->sendConfirmation();
        $applicant->scheduleInterview(now()->addWeek(), 'Head office');
        $applicant->moveToNextStage(); // Applied -> Interviewed
        $applicant->moveToNextStage(); // Interviewed -> Shortlisted
        $applicant->reject();

        Notification::assertSentOnDemandTimes(ApplicantUpdate::class, 4);
        Notification::assertSentOnDemand(ApplicantUpdate::class, fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'candidate@example.test'
            && str_contains($n->subject, 'Interview invitation'));
        expect($applicant->fresh()->interview_location)->toBe('Head office')
            ->and($applicant->fresh()->status)->toBe('Rejected');
    });

    it('creates the employee when an applicant is moved from Shortlisted to Hired', function () {
        Notification::fake();
        Role::findOrCreate('Employee', 'web');
        $applicant = makeApplicant();
        $applicant->update(['status' => 'Shortlisted']);

        $applicant->moveToNextStage();

        expect($applicant->fresh()->status)->toBe('Hired')
            ->and(Employee::where('email', 'candidate@example.test')->exists())->toBeTrue();
    });

    it('sends a new hire a set-password link rather than an unusable login', function () {
        Notification::fake();
        Role::findOrCreate('Employee', 'web');
        $applicant = makeApplicant();

        $applicant->hire();

        $user = User::where('email', 'candidate@example.test')->firstOrFail();
        Notification::assertSentTo($user, WelcomeNewHire::class, fn ($n) => str_contains($n->setPasswordUrl, 'reset'));
    });
});

describe('email notifications', function () {
    it('delivers by email by default and skips email for users who opted out, keeping the in-app copy', function () {
        $wants = User::factory()->create(['email_notifications' => true]);
        $optedOut = User::factory()->create(['email_notifications' => false]);
        $notification = new UserNotification('Hi', 'There', channels: ['filament', 'email']);

        expect($notification->via($wants))->toBe(['mail', 'database'])
            ->and($notification->via($optedOut))->toBe(['database']);
    });

    it('emails the manager about a new leave request', function () {
        Notification::fake();
        $manager = makeEmployee();
        $employee = makeEmployee(['manager_id' => $manager->id]);

        Leave::create(['employee_id' => $employee->id, 'leave_type_id' => makeLeaveType()->id, 'start_date' => '2030-04-01', 'end_date' => '2030-04-02', 'status' => 'Pending']);

        Notification::assertSentTo($manager->user, UserNotification::class, fn ($n, $channels) => in_array('mail', $channels));
    });
});

describe('reminders', function () {
    it('reminds a manager about leave requests that have waited too long, and not about fresh ones', function () {
        Notification::fake();
        $manager = makeEmployee();
        $staleEmployee = makeEmployee(['manager_id' => $manager->id]);
        $freshEmployee = makeEmployee(['manager_id' => makeEmployee()->id]);
        $type = makeLeaveType();
        $stale = Leave::create(['employee_id' => $staleEmployee->id, 'leave_type_id' => $type->id, 'start_date' => '2030-04-01', 'end_date' => '2030-04-02', 'status' => 'Pending']);
        $stale->forceFill(['created_at' => now()->subDays(5)])->saveQuietly();
        Leave::create(['employee_id' => $freshEmployee->id, 'leave_type_id' => $type->id, 'start_date' => '2030-04-01', 'end_date' => '2030-04-02', 'status' => 'Pending']);
        Notification::fake(); // forget the "new request" notifications created above

        $this->artisan('hr:send-reminders')->assertSuccessful();

        Notification::assertSentTo($manager->user, UserNotification::class, fn ($n) => str_contains($n->title, 'Leave requests awaiting'));
        Notification::assertSentToTimes($freshEmployee->manager->user, UserNotification::class, 0);
    });
});
