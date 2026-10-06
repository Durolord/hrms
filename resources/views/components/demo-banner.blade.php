@php
    $resetAt = \App\Support\Demo::nextResetAt();
@endphp

<div
    role="status"
    x-data="{
        resetAt: {{ $resetAt->getTimestamp() * 1000 }},
        label: @js(\App\Support\Demo::resetsIn()),
        tick() {
            const seconds = Math.max(0, Math.round((this.resetAt - Date.now()) / 1000));
            if (seconds === 0) { this.label = 'a moment'; return; }
            if (seconds < 60) { this.label = seconds + (seconds === 1 ? ' second' : ' seconds'); return; }
            const minutes = Math.ceil(seconds / 60);
            if (minutes < 120) { this.label = minutes + (minutes === 1 ? ' minute' : ' minutes'); return; }
            const hours = Math.floor(minutes / 60);
            this.label = hours + ' hours ' + (minutes % 60) + ' minutes';
        },
    }"
    x-init="tick(); setInterval(() => tick(), 1000)"
    class="w-full bg-amber-500 px-4 py-1 text-center text-xs font-medium text-black sm:text-sm"
>
    Live demo. Explore freely: all data resets in
    <span x-text="label" class="font-semibold tabular-nums">{{ \App\Support\Demo::resetsIn() }}</span>.
    <a href="https://durolord.com/projects/hr-management-system" class="underline">About this project</a>
</div>
