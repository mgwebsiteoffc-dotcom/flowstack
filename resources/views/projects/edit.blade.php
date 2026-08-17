@extends('layouts.app')
@section('title', 'Edit project')
@section('content')
<form method="POST" action="{{ route('projects.update', $project) }}" class="max-w-3xl space-y-6">
    @csrf
    @method('PATCH')
    <x-card title="Project details" icon="📁">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Project name *</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                <select name="client_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $project->client_id) == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Project::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', $project->status) === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Service type</label>
                <select name="service_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach (\App\Models\ClientService::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('service_type', $project->service_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Start date</label>
                <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->toDateString()) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">End date</label>
                <input type="date" name="end_date" value="{{ old('end_date', $project->end_date?->toDateString()) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('description', $project->description) }}</textarea>
            </div>
        </div>
    </x-card>
    <div class="flex justify-end gap-3">
        <a href="{{ route('projects.show', $project) }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save changes</button>
    </div>
</form>
@endsection
