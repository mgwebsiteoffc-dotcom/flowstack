@extends('layouts.app')
@section('title', 'Settings')
@section('breadcrumb', 'Settings')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>
        @include('settings.partials.nav', ['active' => 'general'])
    </div>
    <div class="lg:col-span-3">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-card title="Agency settings" icon="building-office">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Agency name</label>
                        <input type="text" name="name" value="{{ old('name', $tenant?->name) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $tenant?->email) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $tenant?->phone) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="address" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('address', $tenant?->address) }}</textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        <input type="file" name="logo" accept="image/*" class="text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                        <select name="timezone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach (['Asia/Kolkata', 'UTC', 'Asia/Dubai', 'America/New_York', 'Europe/London', 'Australia/Sydney'] as $tz)
                                <option value="{{ $tz }}" {{ ($settings['timezone'] ?? 'Asia/Kolkata') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select name="currency" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach (['INR' => '₹ INR', 'USD' => '$ USD', 'AED' => 'AED'] as $code => $label)
                                <option value="{{ $code }}" {{ ($settings['currency'] ?? 'INR') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Date format</label>
                        <input type="text" name="date_format" value="{{ old('date_format', $settings['date_format'] ?? 'd M Y') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                </div>
            </x-card>

            <x-card title="Invoicing defaults" icon="receipt">
                <div class="grid sm:grid-cols-3 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Invoice prefix</label>
                        <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Starting number</label>
                        <input type="number" name="invoice_start_number" value="{{ old('invoice_start_number', $settings['invoice_start_number'] ?? 1001) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Payment terms (days)</label>
                        <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', $settings['payment_terms_days'] ?? 15) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Default tax rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $settings['tax_rate'] ?? 18) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Invoice footer text</label>
                        <input type="text" name="invoice_footer" value="{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                </div>
            </x-card>

            <x-card title="Bank details (printed on invoices)" icon="building-library">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Bank name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $settings['bank_name'] ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Account number</label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $settings['bank_account_number'] ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">IFSC</label>
                        <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc', $settings['bank_ifsc'] ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Beneficiary</label>
                        <input type="text" name="bank_beneficiary" value="{{ old('bank_beneficiary', $settings['bank_beneficiary'] ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                </div>
            </x-card>

            <div class="flex justify-end">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save settings</button>
            </div>
        </form>

        @if (auth()->user()->isAdmin())
            @php
                $financialRoles = json_decode((string) \App\Models\Setting::get('financial_roles'), true);
                $financialRoles = is_array($financialRoles) ? $financialRoles : ['ops_manager'];
            @endphp
            <form method="POST" action="{{ route('settings.financial-visibility') }}" class="space-y-6 mt-6">
                @csrf
                <x-card title="Billing visibility" icon="lock-closed">
                    <p class="text-sm text-gray-500 mb-4">
                        Choose which roles can see payment &amp; billing terms (amounts, rates, costs, billable flags, retainer values).
                        By default these are <strong>masked</strong> for everyone except admins and the finance manager.
                    </p>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" checked disabled class="rounded accent-indigo-600"> Admin <span class="text-xs text-gray-400">(always)</span>
                        </label>
                        @foreach (['ops_manager' => 'Ops / Finance manager', 'account_manager' => 'Account manager', 'specialist' => 'Specialist / employee'] as $role => $label)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="roles[]" value="{{ $role }}" {{ in_array($role, $financialRoles, true) ? 'checked' : '' }} class="rounded accent-indigo-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </x-card>
                <div class="flex justify-end">
                    <button class="bg-gray-900 text-white px-6 py-2 rounded-lg text-sm font-medium">Save billing visibility</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
