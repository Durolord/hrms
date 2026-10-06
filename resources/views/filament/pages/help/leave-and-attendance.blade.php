<x-filament-panels::page>
    <div class="space-y-6 text-sm leading-6 text-gray-700 dark:text-gray-300">
        <x-filament::section>
            <x-slot name="heading">Attendance</x-slot>
            <ul class="list-disc space-y-2 ps-5">
                <li>Each employee marks today's attendance from <em>Mark Attendance</em> on the dashboard: time in, break start and end, and time out. Saving again updates the same day.</li>
                <li>HR Managers and Admins can view and correct everyone's records under <em>Employee Management → Attendances</em>, including the calendar and daily overview.</li>
                <li>Every night at 23:55 the day is rolled up into attendance summaries used by the dashboard and reports.</li>
            </ul>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Requesting leave</x-slot>
            <p>Use <em>Make Request</em> in <em>My Leaves</em>. A request is accepted only when:</p>
            <ul class="mt-2 list-disc space-y-2 ps-5">
                <li>the end date is on or after the start date, and a half day covers a single date;</li>
                <li>the dates contain at least one working day (weekends and <em>Holidays</em> don't count);</li>
                <li>it doesn't overlap another pending or approved request;</li>
                <li>the remaining balance covers it. The balance is the leave type's yearly days (30 unless set) minus approved <em>and pending</em> days this year.</li>
            </ul>
            <p class="mt-2">Your line manager is notified straight away. You can cancel a request while it is still pending.</p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Deciding leave</x-slot>
            <ul class="list-disc space-y-2 ps-5">
                <li><strong>Who decides:</strong> the employee's line manager (a Department Head sees their direct reports in their branch), plus any HR Manager or Admin.</li>
                <li>Approve or reject from the list (one or in bulk) or from the request itself. A rejection needs a reason, which is sent to the employee.</li>
                <li>Once approved or rejected, a request can't change status again; file a new request instead.</li>
                <li>If the leave type has a per-day deduction, approval adds a deduction of <em>days × amount</em> to that month's payroll (see <x-filament::link :href="\App\Filament\Pages\Help\PayrollGuide::getUrl()">Payroll</x-filament::link>).</li>
                <li>Requests waiting two or more days are included in the 08:00 weekday reminder digest.</li>
            </ul>
        </x-filament::section>

        @if ($this->isDemo())
            <x-filament::section>
                <x-slot name="heading">Try it</x-slot>
                <ol class="list-decimal space-y-3 ps-5">
                    <li>@include('filament.pages.help._demo-as', ['role' => 'Employee']) Request leave that overlaps a weekend and see only working days counted; then try overlapping dates to see the rule kick in.</li>
                    <li>@include('filament.pages.help._demo-as', ['role' => 'Department Head']) Approve that request and bulk-reject the others waiting from your team.</li>
                    <li>@include('filament.pages.help._demo-as', ['role' => 'HR Manager']) Set a per-day deduction on <em>Unpaid Leave</em> under <em>Leave Types</em>, approve an unpaid request and find the deduction under <em>Deductions</em>.</li>
                </ol>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
