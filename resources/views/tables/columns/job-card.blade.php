@php
    $job = $getRecord();
    $isNew = $job->created_at->gt(now()->subDays(7));
    $skills = $job->skills->take(4);
    $moreSkills = max(0, $job->skills->count() - $skills->count());
@endphp

<article class="group flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-md transition duration-200 hover:-translate-y-1 hover:border-primary-500/50 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-start justify-between gap-3">
        <h3 class="text-lg font-semibold leading-snug text-gray-950 dark:text-white">
            {{ $job->title }}
        </h3>
        @if ($isNew)
            <span class="shrink-0 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 ring-1 ring-success-600/20 dark:bg-success-500/10 dark:text-success-400">
                New
            </span>
        @endif
    </div>

    <div class="mt-3 flex flex-wrap gap-2 text-xs">
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <x-heroicon-m-map-pin class="h-3.5 w-3.5" /> {{ $job->branch?->name ?? 'Remote' }}
        </span>
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <x-heroicon-m-building-office class="h-3.5 w-3.5" /> {{ $job->department?->name }}
        </span>
    </div>

    <p class="mt-4 line-clamp-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
        {{ \Illuminate\Support\Str::limit(strip_tags((string) $job->description), 200) }}
    </p>

    @if ($skills->isNotEmpty())
        <div class="mt-4 flex flex-wrap gap-1.5">
            @foreach ($skills as $skill)
                <span class="rounded-md bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                    {{ $skill->name }}
                </span>
            @endforeach
            @if ($moreSkills)
                <span class="rounded-md px-2 py-0.5 text-xs text-gray-500">+{{ $moreSkills }}</span>
            @endif
        </div>
    @endif

    <div class="mt-auto flex items-center justify-between pt-5">
        <span class="text-xs text-gray-500">Posted {{ $job->created_at->diffForHumans() }}</span>
        <a
            href="{{ route('jobs.apply', ['record' => $job->id]) }}"
            class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3.5 py-2 text-sm font-medium text-white transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
        >
            View &amp; apply
            <x-heroicon-m-arrow-right class="h-4 w-4 transition group-hover:translate-x-0.5" />
        </a>
    </div>
</article>
