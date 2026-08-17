@extends('layouts.app')
@section('title', 'New lead')
@section('content')
<form method="POST" action="{{ route('leads.store') }}" class="max-w-3xl space-y-6">
    @csrf
    <x-card title="Lead details" icon="target">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Contact name *</label>
                <input type="text" name="contact_name" value="{{ old('contact_name') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Company name</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                <select name="source_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Lead::SOURCE_TYPES as $s)
                        <option value="{{ $s }}" {{ old('source_type', 'manual') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Lead source detail</label>
                <input type="text" name="lead_source" value="{{ old('lead_source') }}" placeholder="e.g. Referral, Website, Meta Ads…" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Pipeline stage</label>
                <select name="stage_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" {{ old('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Assignee</label>
                <select name="assigned_to" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Estimated value (₹)</label>
                <input type="number" step="0.01" name="estimated_value" value="{{ old('estimated_value') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Probability (%)</label>
                <input type="number" name="probability" min="0" max="100" value="{{ old('probability', 20) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Expected close date</label>
                <input type="date" name="expected_close_date" value="{{ old('expected_close_date') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Services interested</label>
                <div class="space-y-1">
                    @foreach (\App\Models\ClientService::TYPES as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="services_interested[]" value="{{ $key }}" class="rounded">{{ $label }}
                        </label>
                    @endforeach
                </div></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea></div>
        </div>
    </x-card>
    <div class="flex justify-end gap-3">
        <a href="{{ route('leads.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Create lead</button>
    </div>
</form>
@endsection
