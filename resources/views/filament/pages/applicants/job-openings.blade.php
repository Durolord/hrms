<x-filament::page>
    {{-- Flatten Filament's own record/table cards so each job renders as a single card. --}}
    <style>
        .jobs-board .fi-ta-ctn { background: transparent; box-shadow: none; --tw-ring-shadow: 0 0 #0000; border: 0; }
        .jobs-board .fi-ta-record { background: transparent !important; box-shadow: none !important; --tw-ring-shadow: 0 0 #0000 !important; border-radius: 1rem; }
        .jobs-board .fi-ta-record > div > div { padding-block: 0; }
        .jobs-board .fi-ta-record .ps-4 { padding-inline: 0; }
        .jobs-board .fi-ta-content { border-top: 0; }
        .jobs-board .fi-ta-header-toolbar { display: none; }
    </style>

    <div class="jobs-board mx-auto w-full max-w-7xl rounded-3xl bg-white/80 p-6 shadow-xl ring-1 ring-gray-950/5 backdrop-blur-md sm:p-10 dark:bg-gray-950/75 dark:ring-white/10">
        <header class="mx-auto mb-8 max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">Careers</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl dark:text-white">
                Join our team
            </h1>
            <p class="mt-3 text-base text-gray-700 dark:text-gray-300">
                Browse our current openings, filter by department or branch, and apply in minutes.
            </p>
        </header>

        {{ $this->table }}
    </div>
</x-filament::page>
