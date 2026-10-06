<x-filament-panels::page>
    @php($reports = $this->reports())

    @foreach ([
        'headcount' => ['Headcount by department', null],
        'leave' => ['Leave usage', 'year'],
        'payroll' => ['Payroll cost by department', 'month'],
    ] as $key => [$title, $filter])
        <x-filament::section :heading="$title">
            <x-slot name="headerEnd">
                <div class="flex items-center gap-3">
                    @if ($filter === 'year')
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="year">
                                @foreach (range(now()->year, now()->year - 4) as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    @elseif ($filter === 'month')
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="month">
                                @foreach ($this->monthOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    @endif
                    <x-filament::button size="sm" color="gray" icon="heroicon-m-arrow-down-tray" wire:click="export('{{ $key }}')">
                        CSV
                    </x-filament::button>
                </div>
            </x-slot>

            @if ($reports[$key]->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No data for this period.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/10">
                                @foreach (array_keys($reports[$key]->first()) as $heading)
                                    <th class="px-3 py-2 font-semibold text-gray-950 dark:text-white">{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports[$key] as $row)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                    @foreach ($row as $heading => $value)
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                            @if ($heading === 'Net pay')
                                                ₦ {{ number_format($value, 2) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endforeach
</x-filament-panels::page>
