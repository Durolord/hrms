{{--
    Favicon that follows the active theme. Without JS the media queries follow the OS setting; with JS the
    icon tracks the `dark` class Filament puts on <html>, so it also follows Filament's own theme switcher.
--}}
@php
    $light = asset('images/brand/favicon-light.svg');
    $dark = asset('images/brand/favicon-dark.svg');
@endphp
<link rel="icon" type="image/svg+xml" href="{{ $light }}" media="(prefers-color-scheme: light)" data-themed-favicon>
<link rel="icon" type="image/svg+xml" href="{{ $dark }}" media="(prefers-color-scheme: dark)" data-themed-favicon>
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">
<script>
    (() => {
        const root = document.documentElement;
        const sync = () => {
            const href = root.classList.contains('dark') ? @js($dark) : @js($light);
            document.querySelectorAll('link[data-themed-favicon]').forEach((link) => {
                link.removeAttribute('media');
                if (link.getAttribute('href') !== href) link.setAttribute('href', href);
            });
        };
        new MutationObserver(sync).observe(root, { attributes: true, attributeFilter: ['class'] });
        document.addEventListener('DOMContentLoaded', sync);
        sync();
    })();
</script>
