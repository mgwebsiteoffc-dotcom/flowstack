<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Task365') · {{ app('currentTenant')?->name ?? 'Task365' }}</title>

    {{-- PWA / install-as-app --}}
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Task365">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body x-data="{ sidebarOpen: window.innerWidth >= 768 }" class="bg-gray-100 min-h-screen overflow-x-hidden">
    @include('components.app-intro')
    @include('components.sidebar')
    {{-- Mobile backdrop: dims the page while the sidebar drawer is open --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="md:hidden fixed inset-0 bg-black/40 z-30"></div>
    <div :class="sidebarOpen ? 'md:ml-64' : 'md:ml-0'" class="transition-all duration-200 min-h-screen flex flex-col">
        @include('components.topbar')
        @if (session('impersonator_admin'))
            <div class="bg-purple-600 text-white text-xs px-6 py-2 flex items-center justify-center gap-3">
                <span><x-icon name="eye" class="w-4 h-4 inline-block" /> You are impersonating this workspace as a super admin.</span>
                <form method="POST" action="{{ route('super-admin.impersonate.stop') }}">@csrf
                    <button class="font-bold underline hover:no-underline">Exit impersonation</button>
                </form>
            </div>
        @endif
        @php $__tenant = app('currentTenant'); @endphp
        @if ($__tenant && $__tenant->is_trial && $__tenant->trial_ends_at)
            <div class="bg-amber-500 text-white text-xs px-6 py-2 flex items-center justify-center gap-2">
                <x-icon name="hourglass" class="w-4 h-4" /><span>{{ $__tenant->trialDaysRemaining() }} days remaining in your free trial</span>
                <a href="{{ route('upgrade') }}" class="font-bold underline hover:no-underline">Upgrade →</a>
            </div>
        @endif
        <main class="p-6 pb-24 md:pb-6 flex-1">
            @include('components.alert')
            @yield('content')
        </main>
        <footer class="px-6 pb-4 text-xs text-gray-400 leading-relaxed break-words">
            &copy; {{ date('Y') }} {{ app('currentTenant')?->name ?? 'Task365' }} · A product by Akestech Infotech Pvt Ltd · <a href="{{ route('upgrade') }}" class="hover:text-gray-600">Subscription</a>
        </footer>
    </div>
    @include('components.mobile-nav')
    @include('components.toast')
    @stack('scripts')

    <script>
    (function () {
        // PWA: register service worker for installability + offline shell.
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js').catch(function () {});
            });
        }

        // Capture the install prompt so we can surface an "Install app" button.
        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;
            window.dispatchEvent(new CustomEvent('app:installable'));
        });
        window.addEventListener('appinstalled', function () {
            deferredPrompt = null;
        });

        // Let the install buttons trigger the prompt via the stored event.
        window.installApp = function () {
            if (!deferredPrompt) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Open your browser menu and choose \u201CAdd to Home Screen\u201D to install.', type: 'info' } }));
                return Promise.resolve();
            }
            deferredPrompt.prompt();
            return deferredPrompt.userChoice.then(function () { deferredPrompt = null; });
        };

        // ---- Push notifications ----
        window.task365Push = {
            vapidPublicKey: @json(config('services.push.vapid.public_key')),
            supported: ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window),
            async isSubscribed() {
                if (!this.supported) return false;
                const reg = await navigator.serviceWorker.getRegistration();
                if (!reg) return false;
                const sub = await reg.pushManager.getSubscription();
                return !!sub;
            },
            async enable() {
                if (!this.supported) throw new Error('Push notifications are not supported on this browser.');
                if (!this.vapidPublicKey) throw new Error('Push notifications are not configured on this server yet.');

                const permission = await Notification.requestPermission();
                if (permission !== 'granted') throw new Error('Notification permission was denied.');

                const reg = await navigator.serviceWorker.register('/sw.js');
                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(this.vapidPublicKey),
                    });
                }

                await fetch('/push/subscribe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(sub.toJSON ? sub.toJSON() : sub),
                });

                return true;
            },
            async disable() {
                const reg = await navigator.serviceWorker.getRegistration();
                if (!reg) return;
                const sub = await reg.pushManager.getSubscription();
                if (sub) {
                    await fetch('/push/unsubscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ endpoint: sub.endpoint }),
                    });
                    await sub.unsubscribe();
                }
            },
        };

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const output = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) output[i] = rawData.charCodeAt(i);
            return output;
        }
    })();
    </script>
</body>
</html>
