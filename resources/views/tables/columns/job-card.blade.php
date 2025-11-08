<div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
<h3
    class="text-lg font-semibold text-gray-600 dark:text-gray-400"
    style="--c-400:var(--gray-400); --c-600:var(--gray-600);"
>
    {{ $getRecord()->title }}
</h3>

    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
        📍 {{ $getRecord()->branch->name }}, {{ $getRecord()->department->name }}
    </p>

    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
        Posted: {{ $getRecord()->created_at->format('d/m/Y') }}
    </p>

    <p class="mt-3 text-gray-700 dark:text-gray-300 leading-relaxed">
        {{ $getRecord()->description }}
    </p>

    <div class="mt-4">
        <a
            href="{{ route('jobs.apply', ['record' => $getRecord()->id]) }}"
            class="text-primary-600 dark:text-primary-400 font-medium hover:underline focus:outline-none focus:ring-2 focus:ring-primary-500 rounded"
        >
            View Details →
        </a>
    </div>
</div>
