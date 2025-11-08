<div class="bg-white p-6 shadow-md rounded-lg">
    <h3 class="text-xl font-semibold">{{ $getRecord()->title }}</h3>
    <p class="text-gray-600">
        📍 {{ $getRecord()->branch->name }}, {{ $getRecord()->department->name }}
    </p>
    <p class="text-sm text-gray-500">Posted: {{ $getRecord()->created_at->format('d/m/Y') }}</p>
    <p class="mt-2">{{ $getRecord()->description }}</p>
    <div class="mt-4">
        <a href="{{ route('jobs.apply', ['record' => $getRecord()->id]) }}" class="text-blue-600 font-semibold text-custom-600 dark:text-custom-400" style="--c-400:var(--primary-400);--c-600:var(--primary-600);">
            View Details →
        </a>
    </div>
</div>