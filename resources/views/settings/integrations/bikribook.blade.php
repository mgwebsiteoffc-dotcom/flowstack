@extends('layouts.app')
@section('title', 'BikriBook Integration')
@section('breadcrumb', 'Settings / Integrations / BikriBook')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'bikribook'])</div>
    <div class="lg:col-span-3 space-y-6">
        <form method="POST" action="{{ route('settings.integrations.bikribook.save') }}" class="space-y-6">
            @csrf
            <x-card title="API credentials" icon="🔐">
                <p class="text-xs text-gray-400 mb-4">Your API key is stored <strong>encrypted</strong> (AES-256). Only the last 4 characters are shown.</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                        <div class="flex items-center gap-2">
                            <input type="password" name="api_key" placeholder="{{ $tenant->bikriBookKeyLastFour() ? '••••••••'.$tenant->bikriBookKeyLastFour().' (leave blank to keep)' : 'Enter API key' }}"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                        <input type="password" name="api_secret" placeholder="•••••••• (leave blank to keep)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Company ID</label>
                        <input type="text" name="company_id" value="{{ $tenant->bikribook_company_id }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base URL</label>
                        <input type="url" name="base_url" value="{{ $tenant->bikribook_base_url ?? config('services.bikribook.base_url') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </x-card>

            <x-card title="Defaults & toggles" icon="⚙️">
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="auto_sync" value="1" class="rounded" {{ ($settings['bikribook_auto_sync'] ?? 1) ? 'checked' : '' }}>
                        Auto-sync payment status every 6 hours
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="auto_create_customer" value="1" class="rounded" {{ ($settings['bikribook_auto_create_customer'] ?? 1) ? 'checked' : '' }}>
                        Auto-create BikriBook customer when a client is added
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="notify_payment" value="1" class="rounded" {{ ($settings['bikribook_notify_payment'] ?? 1) ? 'checked' : '' }}>
                        Notify admin when a payment is received
                    </label>
                </div>
                <div class="grid sm:grid-cols-3 gap-4 mt-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Default tax rate (%)</label>
                        <input type="number" step="0.01" value="{{ $settings['tax_rate'] ?? 18 }}" disabled class="w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-sm">
                        <p class="text-[10px] text-gray-400 mt-1">Set in Agency settings</p></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <input type="text" value="{{ $settings['currency'] ?? 'INR' }}" disabled class="w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-sm">
                        <p class="text-[10px] text-gray-400 mt-1">Set in Agency settings</p></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment terms</label>
                        <input type="text" value="{{ ($settings['payment_terms_days'] ?? 15).' days' }}" disabled class="w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-sm">
                        <p class="text-[10px] text-gray-400 mt-1">Set in Agency settings</p></div>
                </div>
            </x-card>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('settings.integrations.bikribook.test') }}" onclick="event.preventDefault(); document.getElementById('test-form').submit();"
                   class="px-5 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 text-sm">Test Connection</a>
                <form id="test-form" method="POST" action="{{ route('settings.integrations.bikribook.test') }}" class="hidden">@csrf</form>
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save settings</button>
            </div>
        </form>

        <x-card title="Sync log (last 20)" icon="🕓" :padding="false">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-2.5">Time</th><th class="px-4 py-2.5">Invoice</th><th class="px-4 py-2.5">Action</th><th class="px-4 py-2.5">Result</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-2 text-xs text-gray-400">{{ $log->created_at->format('d M H:i') }}</td>
                            <td class="px-4 py-2 text-xs">{{ $log->invoice?->invoice_number ?? '—' }}</td>
                            <td class="px-4 py-2 text-xs capitalize">{{ str_replace('_', ' ', $log->action) }}</td>
                            <td class="px-4 py-2">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $log->status }}</span>
                                @if ($log->error_message)
                                    <span class="text-[10px] text-gray-400 block mt-0.5 truncate max-w-[220px]" title="{{ $log->error_message }}">{{ $log->error_message }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-sm text-gray-400">No sync operations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
</div>
@endsection
