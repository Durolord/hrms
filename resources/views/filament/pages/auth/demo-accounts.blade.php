@php
    use App\Support\Demo;

    $nextReset = Demo::nextResetAt();
@endphp

<div class="mt-6 space-y-3">
    <div class="text-center">
        <p class="text-sm font-semibold text-gray-950 dark:text-white">Try the live demo: sign in as</p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Every account uses the password
            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-950 dark:bg-white/10 dark:text-white">{{ Demo::password() }}</code>.
            Data resets in {{ Demo::resetsIn() }} (at {{ $nextReset->format('H:i') }}).
        </p>
    </div>

    <ul class="grid gap-2">
        @foreach (Demo::accounts() as $account)
            <li>
                <button
                    type="button"
                    wire:click="loginAs(@js($account['email']))"
                    wire:loading.attr="disabled"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-start ring-1 ring-gray-950/10 transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 disabled:opacity-60 dark:ring-white/10 dark:hover:bg-white/5"
                >
                    <span aria-hidden="true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                        {{ str($account['role'])->explode(' ')->map(fn ($word) => mb_substr($word, 0, 1))->join('') }}
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-medium text-gray-950 dark:text-white">{{ $account['role'] }} <span class="font-normal text-gray-500 dark:text-gray-400">· {{ $account['name'] }}</span></span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $account['summary'] }}</span>
                    </span>
                </button>
            </li>
        @endforeach
    </ul>
</div>
