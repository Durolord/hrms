<x-filament-panels::page>
    <div class="space-y-6 text-sm leading-6 text-gray-700 dark:text-gray-300">
        <x-filament::section>
            <x-slot name="heading">Openings and the public jobs page</x-slot>
            <p>
                Each opening belongs to a department, designation and branch, with its qualifications, responsibilities and skills.
                Active openings are listed on the public <x-filament::link :href="route('jobs.show')" target="_blank">jobs page</x-filament::link>,
                where candidates apply with their details and a CV and receive a confirmation email. Switching an opening to inactive takes it off the page.
            </p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">The applicant pipeline</x-slot>
            <ol class="list-decimal space-y-2 ps-5">
                <li><strong>Applied</strong> → <strong>Interviewed</strong> → <strong>Shortlisted</strong> → <strong>Hired</strong>, one step at a time with <em>Next Stage</em>. Shortlisted candidates are emailed.</li>
                <li><em>Schedule Interview</em> records a date and place or meeting link and emails the invitation.</li>
                <li><em>Reject</em> closes the application and sends a polite decline.</li>
                <li><em>Hire</em> creates the person's sign-in account and employee record in the opening's department, designation and branch, gives them the Employee role, welcomes them and notifies HR. It refuses if an account with that email already exists.</li>
            </ol>
            <p class="mt-2">HR Managers and Admins run the pipeline; only roles allowed to view applicants can download CVs.</p>
        </x-filament::section>

        @if ($this->isDemo())
            <x-filament::section>
                <x-slot name="heading">Try it</x-slot>
                <ol class="list-decimal space-y-3 ps-5">
                    <li>Open the <x-filament::link :href="route('jobs.show')" target="_blank">jobs page</x-filament::link> in a private window and apply for a role.</li>
                    <li>@include('filament.pages.help._demo-as', ['role' => 'HR Manager']) Find your application under <em>Applicants</em>, schedule an interview, move it along and hire yourself. You now appear under <em>Employees</em>.</li>
                </ol>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
