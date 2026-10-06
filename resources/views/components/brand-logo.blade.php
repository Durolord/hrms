{{-- Sidebar/topbar brand: the seal in the variant matching Filament's light/dark class, plus the app name. --}}
<span class="flex items-center gap-2">
    <img src="{{ asset('images/brand/logo-light.svg') }}" alt="" class="h-9 w-9 dark:hidden">
    <img src="{{ asset('images/brand/logo-dark.svg') }}" alt="" class="hidden h-9 w-9 dark:block">
    <span class="text-lg font-bold tracking-wide text-gray-950 dark:text-white">HRMS</span>
</span>
