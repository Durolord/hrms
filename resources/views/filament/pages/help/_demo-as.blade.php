{{-- "Sign in as" chip for a walkthrough step; renders nothing outside demo mode. --}}
@if ($account = $this->demoAccount($role))
    <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700 ring-1 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-400/30">
        <x-filament::icon icon="heroicon-m-user" class="h-3.5 w-3.5" />
        Sign in as {{ $account['role'] }} ({{ $account['name'] }})
    </span>
@endif
