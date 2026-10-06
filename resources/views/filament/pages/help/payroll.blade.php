<x-filament-panels::page>
    <div class="space-y-6 text-sm leading-6 text-gray-700 dark:text-gray-300">
        <x-filament::section>
            <x-slot name="heading">How a payroll is built</x-slot>
            <ul class="list-disc space-y-2 ps-5">
                <li><strong>Basic salary</strong> comes from the employee's designation → pay scale. Someone who joined mid-month is paid for the calendar days from their start date; nobody is paid for a month before they started.</li>
                <li><strong>Allowances</strong> belong to a pay scale, so everyone on that scale gets them.</li>
                <li><strong>Deductions and bonuses</strong> belong to one employee and one month (approved paid-by-the-day leave adds deductions automatically).</li>
                @if (collect(config('payroll.statutory'))->filter()->isNotEmpty())
                    <li><strong>Statutory lines</strong> are added as deductions: @foreach (collect(config('payroll.statutory'))->filter() as $name => $rate){{ $name }} {{ $rate }}%@if (! $loop->last), @endif @endforeach of basic salary.</li>
                @else
                    <li><strong>Statutory lines</strong> (pension, PAYE) can be added as a percentage of basic salary with <code>PAYROLL_PENSION_RATE</code> and <code>PAYROLL_TAX_RATE</code>; they are off in this install.</li>
                @endif
                <li>Percentage amounts on allowances, bonuses and deductions are capped at 50%.</li>
            </ul>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Pending → Approved → Paid</x-slot>
            <ol class="list-decimal space-y-2 ps-5">
                <li><strong>Pending</strong>: drafts are generated for all active employees on day {{ config('payroll.generate_on_day') }} of each month at 06:00, or now with <em>Generate Payrolls</em> (or the <em>generate payroll</em> bulk action on Employees). While Pending, <em>Regenerate</em> picks up any change to salary, allowances, bonuses or deductions.</li>
                <li><strong>Approved</strong>: approving regenerates once more, then freezes every line as a snapshot. Later edits to pay scales or allowances can't change it. The employee is notified that the payslip is ready.</li>
                <li><strong>Paid</strong>: mark it paid once the money has gone out.</li>
            </ol>
            <p class="mt-2"><em>Bank transfer file</em> exports approved payrolls for a month (employees without bank details are listed as skipped). PDF payslips are available once a payroll leaves Pending, and employees can only download their own.</p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Who can do what</x-slot>
            <ul class="list-disc space-y-2 ps-5">
                <li><strong>Finance Manager</strong> and <strong>HR Manager</strong>: pay scales, allowances, bonuses, deductions, generating and approving payroll.</li>
                <li><strong>Admin</strong>: everything, plus the payroll trend on the dashboard.</li>
                <li><strong>Everyone else</strong>: their own payslips in <em>My Payrolls</em>.</li>
            </ul>
        </x-filament::section>

        @if ($this->isDemo())
            <x-filament::section>
                <x-slot name="heading">Try it</x-slot>
                <ol class="list-decimal space-y-3 ps-5">
                    <li>@include('filament.pages.help._demo-as', ['role' => 'Finance Manager']) Last month's drafts are waiting. Add a bonus for one employee for that month, regenerate their payroll and check the new net pay, then approve and pay it.</li>
                    <li>@include('filament.pages.help._demo-as', ['role' => 'Finance Manager']) Try editing that pay scale's allowance and regenerating an approved payroll: it stays as approved.</li>
                    <li>@include('filament.pages.help._demo-as', ['role' => 'Employee']) Download your payslip from <em>My Payrolls</em>.</li>
                </ol>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
