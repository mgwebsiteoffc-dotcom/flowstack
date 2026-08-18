{{-- PWA meta tags + service worker registration (shared by app & auth layouts).
     Makes the product installable as a real app (add to home screen).
     NOTE: URLs stay root-relative so they always resolve against the current
     host (every tenant has its own subdomain — APP_URL must not be trusted). --}}
@php $__accent = \App\Support\Brand::accent(); @endphp
<meta name="theme-color" content="{{ $__accent }}">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Task365">
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
}
</script>
