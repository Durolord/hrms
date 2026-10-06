<?php

use App\Models\Allowance;
use App\Models\Bank;
use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\Payroll;
use App\Notifications\PayslipReady;
use App\Services\BankTransferFile;
use App\Services\PayrollProcessingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

function addAllowance(int $payScaleId, float $amount, string $reason = 'Housing'): Allowance
{
    return Model::unguarded(fn () => Allowance::create([
        'pay_scale_id' => $payScaleId,
        'amount' => $amount,
        'reason' => $reason,
        'is_percentage' => false,
    ]));
}

beforeEach(function () {
    config(['payroll.statutory' => ['Pension' => 0.0, 'Tax (PAYE)' => 0.0]]);
    $this->service = app(PayrollProcessingService::class);
});

it('generates a pending payroll with snapshots of allowances, deductions and bonuses', function () {
    $employee = makeEmployee(basicSalary: 100000);
    addAllowance($employee->designation->pay_scale_id, 20000);
    Model::unguarded(function () use ($employee) {
        Deduction::create(['employee_id' => $employee->id, 'amount' => 5000, 'month' => '2030-05-01', 'reason' => 'Loan', 'is_percentage' => false]);
        Bonus::create(['employee_id' => $employee->id, 'amount' => 10000, 'month' => '2030-05-01', 'reason' => 'Target']);
    });

    $payroll = $this->service->generate($employee, '2030-05');

    expect($payroll->status)->toBe('Pending')
        ->and($payroll->current_allowances)->toHaveCount(1)
        ->and($payroll->current_deductions)->toHaveCount(1)
        ->and($payroll->current_bonuses)->toHaveCount(1)
        ->and($payroll->net_salary)->toBe(125000.0);
});

it('regenerating a pending payroll does not duplicate snapshots', function () {
    $employee = makeEmployee();
    addAllowance($employee->designation->pay_scale_id, 1000);

    $this->service->generate($employee, '2030-05');
    $payroll = $this->service->generate($employee, '2030-05');

    expect(Payroll::count())->toBe(1)->and($payroll->current_allowances()->count())->toBe(1);
});

it('refuses to regenerate an approved payroll', function () {
    Notification::fake();
    $employee = makeEmployee();
    $this->service->generate($employee, '2030-05')->markApproved();

    expect(fn () => $this->service->generate($employee, '2030-05'))->toThrow(DomainException::class);
});

it('keeps an approved payslip stable when the pay scale allowances change afterwards', function () {
    Notification::fake();
    $employee = makeEmployee(basicSalary: 100000);
    $allowance = addAllowance($employee->designation->pay_scale_id, 20000);
    $payroll = $this->service->generate($employee, '2030-05');
    $payroll->markApproved();

    $allowance->update(['amount' => 99999]);
    addAllowance($employee->designation->pay_scale_id, 5000, 'Transport');

    expect($payroll->fresh()->net_salary)->toBe(120000.0);
});

it('prorates the basic salary for the month an employee joins', function () {
    // 31-day month, joined on the 16th: 16 days of pay.
    $employee = makeEmployee(['employment_start_date' => '2030-05-16'], basicSalary: 31000);

    expect($this->service->generate($employee, '2030-05')->basic_salary)->toBe(16000.0);
});

it('does not generate a payroll before the employee started', function () {
    $employee = makeEmployee(['employment_start_date' => '2030-06-10']);

    expect(fn () => $this->service->generate($employee, '2030-05'))->toThrow(DomainException::class);
});

it('applies configured statutory deductions on the prorated basic and does not stack them on regeneration', function () {
    config(['payroll.statutory' => ['Pension' => 8.0, 'Tax (PAYE)' => 0.0]]);
    $employee = makeEmployee(basicSalary: 100000);

    $this->service->generate($employee, '2030-05');
    $payroll = $this->service->generate($employee, '2030-05');

    expect($payroll->current_deductions)->toHaveCount(1)
        ->and((float) $payroll->current_deductions->first()->amount)->toBe(8000.0)
        ->and($payroll->net_salary)->toBe(92000.0);
});

it('stamps approval and payment times and emails the payslip on approval', function () {
    Notification::fake();
    $employee = makeEmployee();
    $payroll = $this->service->generate($employee, '2030-05');

    $payroll->markApproved();
    Notification::assertSentTo($employee->user, PayslipReady::class);
    expect($payroll->fresh()->approved_at)->not->toBeNull()->and($payroll->fresh()->paid_at)->toBeNull();

    $payroll->markPaid();
    expect($payroll->fresh()->status)->toBe('Paid')->and($payroll->fresh()->paid_at)->not->toBeNull();
});

it('the payslip email renders with the PDF attached', function () {
    $employee = makeEmployee();
    $payroll = $this->service->generate($employee, '2030-05');

    $mail = (new PayslipReady($payroll))->toMail($employee->user);

    expect($mail->rawAttachments)->toHaveCount(1)
        ->and($mail->rawAttachments[0]['name'])->toEndWith('.pdf')
        ->and(str_starts_with($mail->rawAttachments[0]['data'], '%PDF'))->toBeTrue();
});

it('payroll command drafts payrolls for active employees only and skips finalised ones', function () {
    Notification::fake();
    $active = makeEmployee();
    $inactive = makeEmployee(['active' => false]);
    $approved = makeEmployee();
    $this->service->generate($approved, '2030-05')->markApproved();

    $this->artisan('app:dispatch-generate-payrolls-batch', ['--month' => '2030-05'])
        ->expectsOutputToContain('1 generated, 1 skipped, 0 failed')
        ->assertSuccessful();

    expect(Payroll::where('employee_id', $active->id)->exists())->toBeTrue()
        ->and(Payroll::where('employee_id', $inactive->id)->exists())->toBeFalse();
});

it('builds a bank transfer file from approved payrolls and flags missing bank details', function () {
    Notification::fake();
    $bank = Model::unguarded(fn () => Bank::create(['name' => 'Test Bank', 'code' => '044']));
    $withBank = makeEmployee(['bank_id' => $bank->id, 'account_number' => '0123456789']);
    $withoutBank = makeEmployee();
    $this->service->generate($withBank, '2030-05')->markApproved();
    $this->service->generate($withoutBank, '2030-05')->markApproved();

    $file = app(BankTransferFile::class)->build('2030-05');

    expect($file['filename'])->toBe('bank-transfer-2030-05.csv')
        ->and($file['rows'])->toHaveCount(2)
        ->and($file['rows'][1][3])->toBe('0123456789')
        ->and($file['rows'][1][4])->toBe('100000.00')
        ->and($file['skipped'])->toBe([$withoutBank->name]);
});

it('neutralises spreadsheet formulas in exported cells', function () {
    expect(BankTransferFile::safeCell('=HYPERLINK("x")'))->toBe("'=HYPERLINK(\"x\")")
        ->and(BankTransferFile::safeCell('Plain'))->toBe('Plain')
        ->and(BankTransferFile::safeCell(1234.5))->toBe(1234.5);
});
