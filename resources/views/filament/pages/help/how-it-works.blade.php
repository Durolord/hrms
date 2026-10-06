<x-filament-panels::page>
    <div class="space-y-6 text-sm leading-6 text-gray-700 dark:text-gray-300">
        <x-filament::section>
            <x-slot name="heading">What this app is for</x-slot>
            <p>
                HRMS runs the people side of a small or medium business with several branches: who works here and where,
                when they were at work, the leave they take, what they are paid each month, and who is being hired.
                Each person signs in with their own account and sees only what their role allows.
            </p>
            <p class="mt-2">
                More detail: <x-filament::link :href="\App\Filament\Pages\Help\LeaveAndAttendance::getUrl()">Leave &amp; attendance</x-filament::link>,
                <x-filament::link :href="\App\Filament\Pages\Help\PayrollGuide::getUrl()">Payroll</x-filament::link> and
                <x-filament::link :href="\App\Filament\Pages\Help\RecruitmentGuide::getUrl()">Recruitment</x-filament::link>.
            </p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">The main workflow</x-slot>
            <ol class="list-decimal space-y-3 ps-5">
                <li>
                    <strong>Set up the organisation.</strong> Add branches and departments (with a head per branch),
                    pay scales with a basic salary, designations linked to a pay scale, allowances per pay scale,
                    leave types (yearly allowance and any per-day deduction) and public holidays.
                </li>
                <li>
                    <strong>Add people.</strong> Creating an employee also creates their sign-in account with the same email.
                    Each employee has a department, designation (which sets their pay scale), branch and line manager.
                    Hiring an applicant does the same automatically and gives them the Employee role.
                </li>
                <li>
                    <strong>Day to day.</strong> Employees mark attendance from the dashboard (time in, breaks, time out).
                    Every night at 23:55 the day's attendance is rolled up into summaries.
                </li>
                <li>
                    <strong>Leave.</strong> An employee requests leave; their line manager is notified and approves or rejects it
                    (HR Managers and Admins can decide any request). Approved leave with a per-day deduction adds a
                    deduction to that month's payroll. Weekday reminders at 08:00 chase anything waiting 2+ days.
                </li>
                <li>
                    <strong>Payroll.</strong> Draft payrolls are generated for every active employee on day
                    {{ config('payroll.generate_on_day') }} of the month (or on demand). Finance reviews the draft, approves it
                    (the figures freeze and the payslip is emailed) and then marks it paid. Bank transfer files and PDF payslips come from the same screen.
                </li>
                <li>
                    <strong>Recruitment.</strong> Open roles appear on the public <x-filament::link :href="route('jobs.show')" target="_blank">jobs page</x-filament::link>.
                    Applicants move Applied → Interviewed → Shortlisted → Hired, with interview invitations and outcome emails along the way.
                </li>
                <li>
                    <strong>Oversight.</strong> The dashboard shows what is waiting for you; Reports, the Organisation chart and the Audit log
                    (who changed what, and when) give the bigger picture.
                </li>
            </ol>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Key controls and business rules</x-slot>
            <ul class="list-disc space-y-2 ps-5">
                <li><strong>Roles decide everything.</strong> Menus, buttons and records are filtered by permission; a missing menu item means your role can't use it.</li>
                <li><strong>Branch boundaries.</strong> Unless a role may see other branches, lists of employees and leave are limited to your own branch.</li>
                <li><strong>Leave:</strong> working days exclude weekends and public holidays; requests can't overlap another pending or approved request, can't exceed the remaining balance, and can't change once approved or rejected. See <x-filament::link :href="\App\Filament\Pages\Help\LeaveAndAttendance::getUrl()">Leave &amp; attendance</x-filament::link>.</li>
                <li><strong>Payroll:</strong> a payroll can be regenerated only while Pending; once approved its figures are frozen snapshots, and payslips can only be downloaded after approval. Employees can only open their own. See <x-filament::link :href="\App\Filament\Pages\Help\PayrollGuide::getUrl()">Payroll</x-filament::link>.</li>
                <li><strong>Percentages</strong> on allowances, bonuses and deductions are capped at 50%.</li>
                <li><strong>Hiring</strong> fails if an account with the applicant's email already exists, so nobody gets two logins.</li>
            </ul>
        </x-filament::section>

        <x-filament::section id="roles">
            <x-slot name="heading">Roles</x-slot>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->roles() as $role => $abilities)
                    <div class="rounded-lg p-4 ring-1 ring-gray-950/5 dark:ring-white/10">
                        <p class="font-semibold text-gray-950 dark:text-white">{{ $role }}</p>
                        @if ($account = $this->demoAccount($role))
                            <p class="text-xs text-gray-500 dark:text-gray-400">Demo: {{ $account['name'] }} · {{ $account['email'] }}</p>
                        @endif
                        <ul class="mt-2 list-disc space-y-1 ps-5">
                            @foreach ($abilities as $ability)
                                <li>{{ $ability }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        @if ($this->isDemo())
            <x-filament::section>
                <x-slot name="heading">Try it: a month in ten minutes</x-slot>
                <x-slot name="description">Sign out from the user menu and pick the next account on the login page. Every account uses the password <code>{{ \App\Support\Demo::password() }}</code>; all data resets in {{ \App\Support\Demo::resetsIn() }}.</x-slot>
                <ol class="list-decimal space-y-4 ps-5">
                    <li>
                        @include('filament.pages.help._demo-as', ['role' => 'Employee'])
                        <p class="mt-1">On the dashboard, use <em>Mark Attendance</em> to record today's time in. Then in <em>My Leaves</em> choose <em>Make Request</em> and ask for two days of Vacation Leave next week. Watch the balance in <em>My leave balance</em> drop.</p>
                    </li>
                    <li>
                        @include('filament.pages.help._demo-as', ['role' => 'Department Head'])
                        <p class="mt-1">The bell shows the new request and <em>Waiting for you</em> counts it. Open <em>Employee Management → Leaves</em>, approve the request, and reject another team member's with a reason.</p>
                    </li>
                    <li>
                        @include('filament.pages.help._demo-as', ['role' => 'HR Manager'])
                        <p class="mt-1">In <em>Recruitment and Openings → Applicants</em>, move a candidate to the next stage, schedule an interview, then hire one: they appear under <em>Employees</em> with their own account. Add a public holiday under <em>Holidays</em> and notice leave requests skip it.</p>
                    </li>
                    <li>
                        @include('filament.pages.help._demo-as', ['role' => 'Finance Manager'])
                        <p class="mt-1">Open <em>Payroll and Compensation → Payrolls</em>. Last month's drafts are Pending: open one, add a bonus to that employee (<em>Bonuses → New</em>, same month), <em>Regenerate</em>, then <em>Approve</em> and <em>Pay</em>. Try <em>Bank transfer file</em> for that month and download a payslip.</p>
                    </li>
                    <li>
                        @include('filament.pages.help._demo-as', ['role' => 'Employee'])
                        <p class="mt-1">The leave decision is in the bell, and <em>My Payrolls</em> lists approved payslips to download. Other people's payslips stay out of reach.</p>
                    </li>
                    <li>
                        @include('filament.pages.help._demo-as', ['role' => 'Admin'])
                        <p class="mt-1">Open <em>Admin → Audit Log</em> to see each change you just made and who made it, then browse <em>Reports</em>, the <em>Organisation chart</em> and <em>Roles</em> (read-only in the demo).</p>
                    </li>
                </ol>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
