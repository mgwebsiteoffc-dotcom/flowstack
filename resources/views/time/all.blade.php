@extends('layouts.app')
@section('title', 'All time')
@section('breadcrumb', 'Time / All time')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('time.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Log</a>
        <a href="{{ route('time.my') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">My timesheet</a>
        <a href="{{ route('time.all') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">All time</a>
    </div>
    <form method="GET" class="flex flex-wrap gap-2 text-sm">
        <select name="user_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All members</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>
        <select name="client_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All clients</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
        <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
    </form>
    <a href="{{ route('time.all', array_merge(request()->query(), ['export' => 1])) }}" class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 text-sm">⬇ Export</a>
</div>

<div class="grid lg:grid-cols-4 gap-6">
    <div>
        <x-card title="Summary by member" icon="👥">
            @foreach ($summary as $userId => $data)
                <div class="flex justify-between py-1.5 text-sm">
                    <span class="text-gray-700 truncate">{{ $data['user']?->name }}</span>
                    <span class="font-medium">{{ round($data['minutes'] / 60, 1) }}h</span>
                </div>
            @endforeach
        </x-card>
    </div>
    <div class="lg:col-span-3">
        <x-card title="Entries" icon="🕓" :padding="false">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3">Member</th><th class="px-4 py-3">Task</th><th class="px-4 py-3">Client</th><th class="px-4 py-3 text-right">Hours</th><th class="px-4 py-3">Date</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5">{{ $entry->user?->name }}</td>
                            <td class="px-4 py-2.5 text-gray-700 max-w-[240px] truncate">{{ $entry->task?->title ?? $entry->description ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-500">{{ $entry->client?->company_name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-medium">{{ round($entry->duration_minutes / 60, 2) }}</td>
                            <td class="px-4 py-2.5 text-gray-500">{{ $entry->started_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-sm text-gray-400">No entries match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $entries->links() }}</div>
        </x-card>
    </div>
</div>
@endsection
