@extends('layouts.app')
@section('title', 'Add client')
@section('content')
<form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf
    <x-card title="Company details" icon="building-office">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Company name *</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                <input type="text" name="industry" value="{{ old('industry') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                <input type="url" name="website" value="{{ old('website') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">GSTIN</label>
                <input type="text" name="gstin" value="{{ old('gstin') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                <input type="file" name="logo" accept="image/*" class="text-sm"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('address') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" name="city" value="{{ old('city') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                <input type="text" name="state" value="{{ old('state') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                <input type="text" name="country" value="{{ old('country') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Monthly retainer (₹)</label>
                <input type="number" step="0.01" name="monthly_retainer" value="{{ old('monthly_retainer') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        </div>
    </x-card>

    <x-card title="Contract & account manager" icon="document">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Contract start</label>
                <input type="date" name="contract_start_date" value="{{ old('contract_start_date') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Contract end</label>
                <input type="date" name="contract_end_date" value="{{ old('contract_end_date') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Account manager</label>
                <select name="account_manager_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach ($accountManagers as $am)
                        <option value="{{ $am->id }}" {{ old('account_manager_id') == $am->id ? 'selected' : '' }}>{{ $am->name }} ({{ $am->role }})</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['onboarding', 'active', 'inactive'] as $s)
                        <option value="{{ $s }}" {{ old('status', 'onboarding') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Health score</label>
                <select name="health_score" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['green', 'yellow', 'red'] as $h)
                        <option value="{{ $h }}" {{ old('health_score', 'green') === $h ? 'selected' : '' }}>{{ ucfirst($h) }}</option>
                    @endforeach
                </select></div>
        </div>
    </x-card>

    <x-card title="Services" icon="wrench">
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach (\App\Models\ClientService::TYPES as $key => $label)
                <label class="flex items-center gap-3 border rounded-lg px-3 py-2.5 cursor-pointer hover:border-indigo-400">
                    <input type="checkbox" name="services[]" value="{{ $key }}" class="rounded">
                    <span class="text-sm text-gray-700 flex-1">{{ $label }}</span>
                    <input type="number" step="0.01" name="service_price_{{ $key }}" placeholder="₹/mo" class="w-24 text-xs rounded border border-gray-300 px-2 py-1">
                </label>
            @endforeach
        </div>
    </x-card>

    <x-card title="Primary contact" icon="identification">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                <input type="text" name="contact_designation" value="{{ old('contact_designation') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        </div>
    </x-card>

    <x-card title="Notes" icon="pencil-square">
        <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea>
    </x-card>

    <div class="flex gap-3 justify-end">
        <a href="{{ route('clients.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Create client</button>
    </div>
</form>
@endsection
