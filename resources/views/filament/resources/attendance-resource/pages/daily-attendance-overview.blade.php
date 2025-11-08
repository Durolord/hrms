<x-filament-panels::page>
    <div class="space-y-6">
        <header class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">
                Attendance for {{ \Illuminate\Support\Carbon::parse($this->date)->toFormattedDateString() }}
            </h2>
            {{-- Optionally, include a date picker or navigation controls --}}
        </header>
        {{-- Render the Filament table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>