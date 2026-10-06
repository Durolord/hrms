<?php

use App\Filament\Resources\ApplicantResource\Pages\ListApplicants;
use App\Filament\Resources\ApplicantResource\Pages\ViewApplicant;
use App\Filament\Resources\HolidayResource\Pages\ManageHolidays;
use App\Filament\Resources\LeaveResource\Pages\CreateLeave;
use App\Filament\Resources\LeaveResource\Pages\ListLeaves;
use App\Filament\Resources\LeaveResource\Pages\ViewLeave;
use App\Filament\Resources\PayrollResource\Pages\ListPayrolls;
use App\Filament\Resources\PayrollResource\Pages\ViewPayroll;
use App\Filament\Widgets\AnniversariesWidget;
use App\Filament\Widgets\MyLeaveBalanceWidget;
use App\Filament\Widgets\PendingApprovalsWidget;
use App\Filament\Widgets\WhosOutWidget;
use App\Models\Applicant;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Opening;
use App\Notifications\ApplicantUpdate;
use App\Services\PayrollProcessingService;
use Database\Seeders\ExtraPermissionsSeeder;
use Database\Seeders\ShieldSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(ShieldSeeder::class);
    $this->seed(ExtraPermissionsSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $this->admin = makeEmployee(['name' => 'Admin Person', 'email' => 'admin@example.test']);
    $this->admin->user->assignRole('Admin');
    $this->actingAs($this->admin->user);
    $this->type = makeLeaveType(['max_days' => 10]);
});

function pendingLeave(string $start = '2030-04-01', string $end = '2030-04-02'): Leave
{
    return Leave::create([
        'employee_id' => makeEmployee()->id,
        'leave_type_id' => test()->type->id,
        'start_date' => $start,
        'end_date' => $end,
        'status' => 'Pending',
    ]);
}

describe('leave', function () {
    it('lists, views, approves and rejects leave from the UI', function () {
        Notification::fake();
        $toApprove = pendingLeave();
        $toReject = pendingLeave('2030-05-01', '2030-05-01');

        expect(livewire(ListLeaves::class)->assertSuccessful()->instance()->getTableRecords()->pluck('id')->all())
            ->toEqualCanonicalizing([$toApprove->id, $toReject->id]);

        livewire(ViewLeave::class, ['record' => $toApprove->getRouteKey()])
            ->assertSuccessful()
            ->callAction('approve');
        livewire(ViewLeave::class, ['record' => $toReject->getRouteKey()])
            ->callAction('reject', ['rejection_reason' => 'Short staffed'])
            ->assertHasNoActionErrors();

        expect($toApprove->fresh())->status->toBe('Approved')->approver_id->toBe($this->admin->user->id)
            ->and($toReject->fresh())->status->toBe('Rejected')->rejection_reason->toBe('Short staffed');

        livewire(ViewLeave::class, ['record' => $toReject->getRouteKey()])->assertSuccessful();
    });

    it('requires a reason to reject', function () {
        $leave = pendingLeave();

        livewire(ViewLeave::class, ['record' => $leave->getRouteKey()])
            ->callAction('reject', ['rejection_reason' => ''])
            ->assertHasActionErrors(['rejection_reason' => 'required']);
        expect($leave->fresh()->status)->toBe('Pending');
    });

    it('bulk approves only pending requests', function () {
        Notification::fake();
        $pending = pendingLeave();
        $rejected = pendingLeave('2030-06-03', '2030-06-04');
        $rejected->reject(null, 'no');

        livewire(ListLeaves::class)
            ->callTableBulkAction('Bulk Approve', [$pending, $rejected]);

        expect($pending->fresh()->status)->toBe('Approved')->and($rejected->fresh()->status)->toBe('Rejected');
    });

    it('stops a create-form submission that overlaps existing leave', function () {
        $existing = pendingLeave('2030-04-01', '2030-04-05');

        livewire(CreateLeave::class)
            ->fillForm([
                'employee_id' => $existing->employee_id,
                'leave_type_id' => $this->type->id,
                'start_date' => '2030-04-03',
                'end_date' => '2030-04-04',
            ])
            ->call('create')
            ->assertHasFormErrors(['start_date']);
        expect(Leave::count())->toBe(1);
    });
});

describe('payroll', function () {
    it('approves then pays a payroll from the view page and freezes the figures', function () {
        Notification::fake();
        config(['payroll.statutory' => ['Pension' => 0.0, 'Tax (PAYE)' => 0.0]]);
        $payroll = app(PayrollProcessingService::class)->generate(makeEmployee(), now()->format('Y-m'));

        livewire(ListPayrolls::class)->assertSuccessful();
        livewire(ViewPayroll::class, ['record' => $payroll->getRouteKey()])
            ->assertSuccessful()
            ->callAction('nextStep');
        expect($payroll->fresh()->status)->toBe('Approved');

        livewire(ViewPayroll::class, ['record' => $payroll->getRouteKey()])->callAction('nextStep');
        expect($payroll->fresh())->status->toBe('Paid')->paid_at->not->toBeNull();
    });
});

describe('recruitment', function () {
    it('schedules an interview from the applicants table and emails the applicant', function () {
        Notification::fake();
        $template = makeEmployee();
        $applicant = Model::unguarded(function () use ($template) {
            $opening = Opening::create(['title' => 'Accountant', 'department_id' => $template->department_id, 'designation_id' => $template->designation_id, 'branch_id' => $template->branch_id]);

            return Applicant::create(['name' => 'Ada', 'email' => 'ada@example.test', 'phone' => '080', 'opening_id' => $opening->id])->fresh();
        });

        livewire(ListApplicants::class)
            ->assertSuccessful()
            ->callTableAction('schedule_interview', $applicant, ['interview_at' => now()->addWeek()->format('Y-m-d H:i:s'), 'interview_location' => 'Lagos office'])
            ->assertHasNoTableActionErrors();

        Notification::assertSentOnDemand(ApplicantUpdate::class);
        expect($applicant->fresh()->interview_location)->toBe('Lagos office');
    });

    it('views an applicant in any stage without error', function (string $status) {
        $template = makeEmployee();
        $applicant = Model::unguarded(function () use ($template, $status) {
            $opening = Opening::create(['title' => 'Accountant', 'department_id' => $template->department_id, 'designation_id' => $template->designation_id, 'branch_id' => $template->branch_id]);

            return Applicant::create(['name' => 'Ada', 'email' => 'ada@example.test', 'phone' => '080', 'opening_id' => $opening->id, 'status' => $status]);
        });

        livewire(ViewApplicant::class, ['record' => $applicant->getRouteKey()])->assertSuccessful();
    })->with(['Applied', 'Interviewed', 'Shortlisted', 'Hired', 'Rejected']);
});

describe('holidays and widgets', function () {
    it('lets an admin manage public holidays', function () {
        livewire(ManageHolidays::class)
            ->assertSuccessful()
            ->callAction('create', ['name' => 'Founders Day', 'date' => '2030-08-01'])
            ->assertHasNoActionErrors();

        expect(Holiday::where('name', 'Founders Day')->exists())->toBeTrue();
    });

    it('renders the dashboard widgets', function () {
        pendingLeave();
        $onLeave = Leave::create([
            'employee_id' => makeEmployee()->id,
            'leave_type_id' => $this->type->id,
            'start_date' => now()->startOfDay(),
            'end_date' => now()->addDay()->startOfDay(),
            'status' => 'Approved',
        ]);
        $onLeave->employee->update(['department_id' => $this->admin->department_id]);

        livewire(PendingApprovalsWidget::class)->assertSuccessful()->assertSee('Leave requests');
        livewire(MyLeaveBalanceWidget::class)->assertSuccessful()->assertSee($this->type->name);
        expect(livewire(WhosOutWidget::class)->assertSuccessful()->instance()->getTableRecords()->pluck('id')->all())->toBe([$onLeave->id]);
        livewire(AnniversariesWidget::class)->assertSuccessful();
    });
});
