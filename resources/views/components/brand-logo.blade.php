{{-- Brand: the seal in the variant matching Filament's light/dark class, plus the wordmark. Sized by brandLogoHeight, identical everywhere. --}}
<div class="flex h-full items-center gap-2.5">
    <img src="{{ asset('images/brand/logo-light.svg') }}" alt="" class="h-full w-auto dark:hidden">
    <img src="{{ asset('images/brand/logo-dark.svg') }}" alt="" class="hidden h-full w-auto dark:block">
    <span class="hrms-wordmark">HRMS</span>
</div>
