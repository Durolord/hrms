<x-filament-panels::page>
    {{-- The widget is rendered automatically from getHeaderWidgets(). --}}
    {{-- Modal for displaying the day’s stats --}}
    <x-filament::modal
        id="attendanceModal"
        wire:model.defer="selectedDate"
        display="slide"
        align="center"
    >
        <x-slot name="heading">
            Attendance Stats for {{ \Illuminate\Support\Carbon::parse($selectedDate)->toFormattedDateString() }}
        </x-slot>
        <div>
            <p>
                Total attendances:
                <strong>{{ $dayStats['total'] ?? 0 }}</strong>
            </p>
        </div>
        <div class="mt-4 text-right">
            {{-- Link to the detailed page, passing the selected date --}}
            <a href=""
               class="text-primary-600 hover:underline">
                View Detailed Information
            </a>
        </div>
    </x-filament::modal>
    @push('scripts')
        <script>
            // Listen for the browser event dispatched in the openDayModal() method.
            window.addEventListener('open-attendance-modal', event => {
                // This assumes that your Filament modal component is controlled via AlpineJS.
                // Adjust according to your modal implementation if necessary.
                document.getElementById('attendanceModal').__x.$data.open = true;
            });
        </script>
    @endpush
</x-filament-panels::page>