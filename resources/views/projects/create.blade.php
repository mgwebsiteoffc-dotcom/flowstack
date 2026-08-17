@extends('layouts.app')
@section('title', 'New project')
@section('content')
<form method="POST" action="{{ route('projects.store') }}" class="max-w-3xl space-y-6">
    @csrf
    <x-card title="Project details" icon="folder">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Project name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                <select name="client_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Select client…</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\Project::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', 'active') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Service type</label>
                <select name="service_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach (\App\Support\ServiceCatalog::all() as $key => $label)
                        <option value="{{ $key }}" {{ old('service_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Start date</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">End date</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>
        </div>
    </x-card>

    <x-card title="Template (optional)" icon="clipboard">
        <select name="template_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            <option value="">No template</option>
            @foreach ($templates as $template)
                <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>{{ $template->name }} ({{ $template->templateTasks->count() }} tasks)</option>
            @endforeach
        </select>
        <p class="text-xs text-gray-400 mt-2">Selecting a template seeds the project with its standard tasks.</p>
    </x-card>

    <x-card title="Team members" icon="users">
        <div x-data="{ members: [] }">
            <template x-for="(m, i) in members" :key="i">
                <div class="flex gap-2 mb-2">
                    <select :name="'member_ids[' + i + ']'" x-model="m.user_id" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Select member…</option>
                        @foreach ($teamMembers ?? [] as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                    <select :name="'member_roles[' + i + ']'" x-model="m.role" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="member">Member</option>
                        <option value="lead">Lead</option>
                    </select>
                    <button type="button" @click="members.splice(i, 1)" class="text-red-400">×</button>
                </div>
            </template>
            <button type="button" @click="members.push({ user_id: '', role: 'member' })" class="text-sm text-indigo-600">+ Add member</button>
        </div>
    </x-card>

    <div class="flex justify-end gap-3">
        <a href="{{ route('projects.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Create project</button>
    </div>
</form>
@endsection
