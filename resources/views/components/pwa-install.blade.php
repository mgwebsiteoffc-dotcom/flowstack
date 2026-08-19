@props([
    'label' => 'Install the app',
    'block' => false, // full-width primary button
    'iconOnly' => false, // compact icon button (used in the sidebar footer)
])

{{-- Install Task365 as a real app: triggers the browser's native install prompt
     (beforeinstallprompt) when available, otherwise shows add-to-home-screen
     instructions (iOS Safari has no native prompt). --}}
<div x-data="pwaInstall()" x-init="init()" {{ $attributes->except('class') }}>
    <template x-if="!isInstalled">
        @if ($iconOnly)
            <button type="button" @click="install()" title="{{ $label }}"
                    {{ $attributes->merge(['class' => 'text-gray-500 hover:text-white']) }}>
                <x-icon name="arrow-down-tray" class="w-5 h-5" />
            </button>
        @else
            <button type="button" @click="install()" title="{{ $label }}"
                    {{ $attributes->merge(['class' => ($block ? 'flex w-full items-center justify-center gap-2' : 'inline-flex items-center gap-2').' rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 shadow-sm shadow-indigo-200 transition']) }}>
                <x-icon name="arrow-down-tray" class="w-4 h-4" />
                <span>{{ $label }}</span>
            </button>
        @endif
    </template>

    {{-- Add-to-home-screen instructions (iOS / browsers without a native prompt).
         Teleported to <body> so a transform on any ancestor (e.g. the sidebar
         drawer) can never break the fixed positioning. --}}
    <template x-teleport="body">
    <div x-show="showHelp" x-cloak class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60" @click="showHelp = false"></div>
        <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md p-6 shadow-2xl">
            <button @click="showHelp = false" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600" aria-label="Close">
                <x-icon name="x-mark" class="w-5 h-5" />
            </button>
            <div class="flex items-center gap-3">
                <img src="/icons/icon-192.png" alt="Task365 app icon" class="w-12 h-12 rounded-xl shadow">
                <div>
                    <div class="font-bold text-gray-900">Install Task365</div>
                    <div class="text-xs text-gray-500" x-text="isIOS ? 'iPhone / iPad' : 'This browser'"></div>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-600" x-show="isIOS">
                <p class="mb-3">Task365 works best installed on your home screen — just like a native app:</p>
                <ol class="list-decimal ml-5 space-y-2.5">
                    <li>Tap the <b>Share</b> button <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-gray-100 align-middle mx-0.5"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 12a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM13.5 5.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM13.5 18.75a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM6 12l7.5-6.75M6 12l7.5 6.75" stroke-linecap="round" stroke-linejoin="round"/></svg></span> in Safari's toolbar.</li>
                    <li>Scroll down and tap <b>Add to Home Screen</b>.</li>
                    <li>Tap <b>Add</b> — Task365 is now an app on your home screen.</li>
                </ol>
            </div>
            <div class="mt-4 text-sm text-gray-600" x-show="!isIOS">
                <p>Open your browser menu and choose <b>Install app</b> or <b>Add to Home Screen</b> to pin Task365 to your home screen — it opens full-screen like a native app.</p>
            </div>

            <button @click="showHelp = false" class="mt-5 w-full bg-gray-900 hover:bg-gray-800 text-white rounded-xl py-2.5 text-sm font-semibold transition">
                Got it
            </button>
        </div>
    </div>
    </template>
</div>

@once
<script>
function pwaInstall() {
    return {
        deferredPrompt: null,
        isInstalled: false,
        isIOS: false,
        showHelp: false,
        init() {
            var mq = window.matchMedia('(display-mode: standalone)');
            this.isInstalled = mq.matches || window.navigator.standalone === true;
            this.isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent || '');
            var self = this;
            var onChange = function (e) { self.isInstalled = e.matches; };
            if (mq.addEventListener) {
                mq.addEventListener('change', onChange);
            } else if (mq.addListener) {
                mq.addListener(onChange);
            }
            window.addEventListener('beforeinstallprompt', function (e) {
                e.preventDefault();
                self.deferredPrompt = e;
            });
            window.addEventListener('appinstalled', function () {
                self.isInstalled = true;
                self.deferredPrompt = null;
            });
        },
        async install() {
            if (this.deferredPrompt) {
                var prompt = this.deferredPrompt;
                this.deferredPrompt = null;
                try {
                    prompt.prompt();
                    var choice = await prompt.userChoice;
                    if (choice && choice.outcome === 'accepted') {
                        this.isInstalled = true;
                    }
                } catch (e) {
                    this.showHelp = true;
                }
            } else {
                this.showHelp = true;
            }
        }
    };
}
</script>
@endonce
