<x-filament::page>
    <div class="mx-auto w-full max-w-4xl rounded-3xl bg-white/85 p-4 shadow-xl ring-1 ring-gray-950/5 backdrop-blur-md sm:p-8 dark:bg-gray-950/75 dark:ring-white/10">
        <a href="{{ route('jobs.show') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">
            <x-heroicon-m-arrow-left class="h-4 w-4" /> All openings
        </a>

        {{ $this->infolist }}
    </div>
</x-filament::page>
