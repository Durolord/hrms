<li>
    <div class="flex flex-wrap items-baseline gap-x-2">
        <span class="font-medium text-gray-950 dark:text-white">{{ $node['employee']->name }}</span>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $node['employee']->designation?->name }}@if ($node['employee']->department) · {{ $node['employee']->department->name }}@endif
        </span>
        @if ($node['reports']->isNotEmpty())
            <span class="text-xs text-gray-400">({{ $node['reports']->count() }} direct {{ \Illuminate\Support\Str::plural('report', $node['reports']->count()) }})</span>
        @endif
    </div>
    @if ($node['reports']->isNotEmpty())
        <ul class="mt-2 ms-4 space-y-2 border-s border-gray-200 ps-4 dark:border-white/10">
            @foreach ($node['reports'] as $child)
                @include('filament.pages.partials.org-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
