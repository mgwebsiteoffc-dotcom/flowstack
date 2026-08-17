@extends('layouts.app')
@section('title', $user->name)
@section('breadcrumb', 'Team / '.$user->name)
@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="space-y-6">
        <x-card title="Profile" icon="👤">
            <div class="flex items-center gap-4 mb-4">
                <x-user-avatar :user="$user" size="lg" />
                <div>
                    <div class="font-bold text-gray-900">{{ $user->name }}</div>
                    <div class="text-sm text-gray-400 capitalize">{{ str_replace('_', ' ', $user->role) }} · {{ $user->designation }}</div>
                </div>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Email</dt><dd>{{ $user->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Phone</dt><dd>{{ $user->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Timezone</dt><dd>{{ $user->timezone }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Hourly cost</dt><dd>₹{{ number_format($user->hourly_cost ?? 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Status</dt><dd>{{ $user->is_active ? 'Active' : 'Inactive' }}</dd></div>
            </dl>
            @can('update', $user)
                <form method="POST" action="{{ route('team.update', $user) }}" class="mt-4 space-y-2 border-t pt-4">
                    @csrf @method('PATCH')
                    <input type="text" name="name" value="{{ $user->name }}" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <input type="text" name="phone" value="{{ $user->phone }}" placeholder="Phone" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <input type="text" name="designation" value="{{ $user->designation }}" placeholder="Designation" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    @can('setRole', App\Models\User::class)
                        <select name="role" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm" onchange="this.form.submit()">
                            @foreach (\App\Models\User::ROLES as $role)
                                <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                            @endforeach
                        </select>
                    @endcan
                    @can('delete', $user)
                        <button name="role" value="{{ $user->role }}" formaction="{{ route('team.toggle', $user) }}" class="w-full text-sm py-1.5 rounded-lg {{ $user->is_active ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    @endcan
                    <button class="w-full bg-indigo-600 text-white rounded-lg py-1.5 text-sm">Save profile</button>
                </form>
            @endcan
        </x-card>

        <x-card title="Performance (this month)" icon="📊">
            <div class="grid grid-cols-3 gap-3 text-center">
                <div><div class="text-xl font-bold text-gray-900">{{ $stats['tasks_completed_month'] }}</div><div class="text-[10px] text-gray-400">Completed</div></div>
                <div><div class="text-xl font-bold text-gray-900">{{ $stats['on_time_rate'] }}%</div><div class="text-[10px] text-gray-400">On-time rate</div></div>
                <div><div class="text-xl font-bold text-gray-900">{{ $stats['hours_logged_month'] }}h</div><div class="text-[10px] text-gray-400">Hours</div></div>
            </div>
        </x-card>

        <x-card title="Assigned clients" icon="🤝">
            @forelse ($clients as $client)
                <a href="{{ route('clients.show', $client) }}" class="block py-1.5 text-sm text-gray-700 hover:text-indigo-600">{{ $client->company_name }}</a>
            @empty
                <p class="text-sm text-gray-400 text-center py-2">No clients assigned</p>
            @endforelse
        </x-card>
    </div>

    <div class="lg:col-span-2">
        <x-card title="Tasks" icon="✅">
            <div class="divide-y divide-gray-50">
                @forelse ($tasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-3 py-2.5 hover:bg-gray-50 px-2 rounded-lg">
                        <x-status-badge :status="$task->status" />
                        <span class="text-sm text-gray-800 flex-1 truncate">{{ $task->title }}</span>
                        <span class="text-xs text-gray-400">{{ $task->client?->company_name }}</span>
                        <span class="text-xs {{ $task->isOverdue() ? 'text-red-500' : 'text-gray-400' }}">{{ $task->due_date?->format('d M') }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">No tasks</p>
                @endforelse
            </div>
        </x-card>
    </div>
</div>
@endsection
