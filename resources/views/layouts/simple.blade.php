@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after' => null,
        'heading' => null,
        'subheading' => null,
        'mode' => 'default',
    ])

    <!-- Wrapper with full-page background and dark/light overlay -->
    <div
        class="flex min-h-screen flex-col relative"
        style="
            background-image: url('/images/bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        "
    >
        <!-- Overlay -->
        <div class="absolute inset-0 z-[-1] bg-white/70 dark:bg-[rgba(5,10,25,0.8)]"></div>

        <div class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center">
            <main class="fi-simple-main w-full min-h-screen px-6 py-12 shadow-sm ring-1 ring-gray-950/5  dark:ring-white/10 sm:rounded-xl sm:px-12">
                <div class="max-w-full md:max-w-fit">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $livewire->getRenderHookScopes()) }}
    </div>
</x-filament-panels::layout.base>
