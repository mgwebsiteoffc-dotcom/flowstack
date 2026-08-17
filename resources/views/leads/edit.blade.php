@extends('layouts.app')
@section('title', 'Edit lead')
@section('content')
<form method="POST" action="{{ route('leads.update', $lead) }}" class="max-w-3xl space-y-6">
    @csrf
    @method('PATCH')
    <x-card title="Lead details" icon="🎯">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Contact name *</label>
                <input type="text" name="contact_name" value="{{ old('contact_name', $lead->contact_name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Company name</label>
                <input type="text" name="company_name" value="{{ old('company_name', $lead->company_name) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $lead->email) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Lead::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', $lead->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Pipeline stage</label>
                <select name="stage_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}" {{ old('stage_id', $lead->stage_id) == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Assignee</label>
                <select name="assigned_to" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Unassigned</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to', $lead->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Estimated value (₹)</label>
                <input type="number" step="0.01" name="estimated_value" value="{{ old('estimated_value', $lead->estimated_value) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Probability (%)</label>
                <input type="number" name="probability" min="0" max="100" value="{{ old('probability', $lead->probability) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Expected close date</label>
                <input type="date" name="expected_close_date" value="{{ old('expected_close_date', $lead->expected_close_date?->toDateString()) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('notes', $lead->notes) }}</textarea></div>
        </div>
    </x-card>
    <div class="flex justify-end gap-3">
        <a href="{{ route('leads.show', $lead) }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save changes</button>
    </div>
</form>
@endsection
