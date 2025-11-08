@php
    $table = $this->table;
    $this->table->header(new \Illuminate\Support\HtmlString(''));
@endphp
<x-filament-widgets::widget class="fi-wi-table">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_START, scopes: static::class) }}
    <x-filament::section
        collapsible
        class="collapsible-table-widget"
        :headerActions="$table->getHeaderActions()"
    >
        <x-slot name="heading">
            {{ $table->getHeading() }}
        </x-slot>
        {{ $this->table }}
    </x-filament::section>
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\Widgets\View\WidgetsRenderHook::TABLE_WIDGET_END, scopes: static::class) }}
</x-filament-widgets::widget>