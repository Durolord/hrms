<?php

use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveRequestValidator;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

function makeLeave(Employee $employee, LeaveType $type, string $start, string $end, array $extra = []): Leave
{
    return Leave::create(array_merge([
        'employee_id' => $employee->id,
        'leave_type_id' => $type->id,
        'start_date' => $start,
        'end_date' => $end,
        'status' => 'Pending',
    ], $extra));
}

it('counts working days excluding weekends and holidays', function () {
    // Mon 2030-01-07 .. Fri 2030-01-11 plus the weekend after
    expect(Leave::countWorkingDays(Carbon::parse('2030-01-07'), Carbon::parse('2030-01-13')))->toBe(5.0);

    Holiday::create(['name' => 'Test Day', 'date' => '2030-01-09']);
    expect(Leave::countWorkingDays(Carbon::parse('2030-01-07'), Carbon::parse('2030-01-13')))->toBe(4.0);
    expect(Leave::countWorkingDays(Carbon::parse('2030-01-07'), Carbon::parse('2030-01-07'), true))->toBe(0.5);
    expect(Leave::countWorkingDays(Carbon::parse('2030-01-12'), Carbon::parse('2030-01-13')))->toBe(0.0);
});

it('does not duplicate the payroll deduction when an approved leave is edited', function () {
    $employee = makeEmployee();
    $type = makeLeaveType(['deduction_amount' => 1000]);
    $leave = makeLeave($employee, $type, '2030-01-07', '2030-01-08');

    $leave->approve();
    $leave->refresh()->update(['is_half_day' => false]);
    $leave->update(['rejection_reason' => 'noise']);

    expect(Deduction::where('employee_id', $employee->id)->count())->toBe(1)
        ->and((float) Deduction::first()->amount)->toBe(2000.0)
        ->and($leave->fresh()->deducted_from_payroll)->toBeTrue();
});

it('records the approver and decision date, and the rejection reason', function () {
    $approver = User::factory()->create();
    $type = makeLeaveType();

    $approved = makeLeave(makeEmployee(), $type, '2030-02-04', '2030-02-05');
    $approved->approve($approver);
    expect($approved->fresh()->approver_id)->toBe($approver->id)
        ->and($approved->fresh()->approved_on)->not->toBeNull();

    $rejected = makeLeave(makeEmployee(), $type, '2030-02-04', '2030-02-05');
    $rejected->reject($approver, 'Busy period');
    expect($rejected->fresh()->status)->toBe('Rejected')
        ->and($rejected->fresh()->rejection_reason)->toBe('Busy period');
});

it('refuses to change the status of a decided leave', function () {
    $leave = makeLeave(makeEmployee(), makeLeaveType(), '2030-03-04', '2030-03-05');
    $leave->reject(null, 'No');

    expect(fn () => $leave->fresh()->approve())->toThrow(ValidationException::class);
    expect($leave->fresh()->status)->toBe('Rejected');
});

it('sends notifications that link to the leave, not the leave type', function () {
    $employee = makeEmployee();
    $leave = makeLeave($employee, makeLeaveType(), '2030-03-04', '2030-03-05');
    $leave->approve();

    $data = $employee->user->notifications()->first()->data;
    expect($data['actions'][0]['url'])->toEndWith('/leaves/'.$leave->id);
});

describe('request validation', function () {
    beforeEach(function () {
        $this->employee = makeEmployee();
        $this->type = makeLeaveType(['max_days' => 3]);
        $this->validate = fn (array $overrides = []) => app(LeaveRequestValidator::class)->validate(array_merge([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->type->id,
            'start_date' => '2030-04-01',
            'end_date' => '2030-04-02',
            'is_half_day' => false,
        ], $overrides));
    });

    it('accepts a valid request and returns the working days', function () {
        expect(($this->validate)())->toBe(2.0);
    });

    it('rejects an end date before the start date', function () {
        expect(fn () => ($this->validate)(['start_date' => '2030-04-05', 'end_date' => '2030-04-02']))
            ->toThrow(ValidationException::class);
    });

    it('rejects a half day spanning several dates', function () {
        expect(fn () => ($this->validate)(['is_half_day' => true]))->toThrow(ValidationException::class);
    });

    it('rejects a weekend-only request', function () {
        expect(fn () => ($this->validate)(['start_date' => '2030-04-06', 'end_date' => '2030-04-07']))
            ->toThrow(ValidationException::class);
    });

    it('rejects overlap with pending and approved leave but not rejected leave', function () {
        $pending = makeLeave($this->employee, $this->type, '2030-04-02', '2030-04-02');
        expect(fn () => ($this->validate)())->toThrow(ValidationException::class);

        $pending->reject(null, 'x');
        expect(($this->validate)())->toBe(2.0);
    });

    it('rejects requests beyond the remaining balance of the requested type', function () {
        makeLeave($this->employee, $this->type, '2030-03-04', '2030-03-05');

        expect(fn () => ($this->validate)(['start_date' => '2030-04-01', 'end_date' => '2030-04-03']))
            ->toThrow(ValidationException::class);
        // A different leave type has its own allowance.
        $other = makeLeaveType(['max_days' => 10]);
        expect(($this->validate)(['leave_type_id' => $other->id, 'end_date' => '2030-04-03']))->toBe(3.0);
    });
});
