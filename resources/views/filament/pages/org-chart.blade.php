<x-filament-panels::page>
    @php($tree = $this->tree())

    @if ($tree->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">No active employees yet.</p>
    @else
        <x-filament::section>
            <ul class="space-y-3">
                @foreach ($tree as $node)
                    @include('filament.pages.partials.org-node', ['node' => $node])
                @endforeach
            </ul>
        </x-filament::section>
    @endif
</x-filament-panels::page>
