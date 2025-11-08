@php
    use Filament\Support\Enums\MaxWidth;
@endphp
<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after' => null,
        'heading' => null,
        'subheading' => null,
        'mode' => 'default', // set to "auth" for Larawind-like auth pages mode
    ])
    <!-- Wrapper with a Tailwind background gradient -->
    <div class="flex min-h-screen flex-col bg-gradient-to-br from-blue-100 via-blue-200 to-blue-300">
        <div class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center">
            <main class="fi-simple-main w-full min-h-screen bg-white px-6 py-12 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:rounded-xl sm:px-12">
                <!-- On small screens, full width; on medium screens, limit width -->
                <div class="max-w-full md:max-w-fit">
                    {{ $slot }}
                </div>
            </main>
        </div>
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $livewire->getRenderHookScopes()) }}
    </div>
</x-filament-panels::layout.base>