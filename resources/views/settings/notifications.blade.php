@extends('layouts.app')
@section('title', 'Notification settings')
@section('breadcrumb', 'Settings / Notifications')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'notifications'])</div>
    <div class="lg:col-span-3">
        <x-card title="Push notifications" icon="device-phone-mobile">
            <p class="text-xs text-gray-400 mb-4">Get task, deadline and comment alerts directly on this device (phone or desktop), even when the app is closed.</p>
            <div x-data="pushToggle()" x-init="init()" class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-800" x-text="state === 'on' ? 'Enabled on this device' : (state === 'off' ? 'Disabled' : 'Checking…')"></div>
                    <div class="text-xs text-gray-400 mt-0.5" x-show="state === 'unavailable'">This browser or server does not support push notifications.</div>
                </div>
                <button type="button" @click="toggle()" :disabled="state === 'busy' || state === 'unavailable'"
                        class="relative w-12 h-7 rounded-full transition-colors duration-200 disabled:opacity-50"
                        :class="state === 'on' ? 'bg-indigo-600' : 'bg-gray-300'">
                    <span class="absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full shadow transition-transform duration-200"
                          :class="state === 'on' ? 'translate-x-5' : ''"></span>
                </button>
            </div>
        </x-card>

        <form method="POST" action="{{ route('settings.notifications.save') }}" class="space-y-6">
            @csrf
            <x-card title="Email notifications" icon="bell">
                <p class="text-xs text-gray-400 mb-4">These preferences apply to your account only. Emails are always sent through the queue.</p>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach ($types as $key => $label)
                        <label class="flex items-center gap-2 border rounded-lg px-3 py-2.5 text-sm text-gray-700 cursor-pointer hover:border-indigo-400">
                            <input type="hidden" name="prefs[{{ $key }}]" value="0">
                            <input type="checkbox" name="prefs[{{ $key }}]" value="1" class="rounded" {{ ($prefs[$key] ?? 1) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </x-card>
            <div class="flex justify-end">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save preferences</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function pushToggle() {
    return {
        state: 'checking',
        async init() {
            const push = window.task365Push;
            if (!push || !push.supported) { this.state = 'unavailable'; return; }
            this.state = (await push.isSubscribed()) ? 'on' : 'off';
        },
        async toggle() {
            const push = window.task365Push;
            const wasOn = this.state === 'on';
            this.state = 'busy';
            try {
                if (wasOn) {
                    await push.disable();
                    this.state = 'off';
                } else {
                    await push.enable();
                    this.state = 'on';
                }
            } catch (e) {
                this.state = (await push.isSubscribed()) ? 'on' : 'off';
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: e.message || 'Could not update notifications', type: 'error' } }));
            }
        }
    }
}
</script>
@endpush
