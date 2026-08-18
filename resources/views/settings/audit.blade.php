@extends('layouts.app')
@section('title', 'Audit log')
@section('breadcrumb', 'Settings / Audit log')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'audit'])</div>
    <div class="lg:col-span-3">
        <form method="GET" class="flex flex-wrap gap-2 text-sm mb-5">
            <select name="user_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
                <option value="">All users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Action contains…" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3">Time</th><th class="px-4 py-3">User</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Details</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-xs text-gray-400 whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-2.5">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5"><code class="text-xs bg-gray-100 rounded px-1.5 py-0.5">{{ $log->action }}</code></td>
                            <td class="px-4 py-2.5 text-xs text-gray-500 max-w-[300px] truncate">
                                @if ($log->new_values) {{ json_encode($log->new_values) }} @else {{ $log->model_type ? class_basename($log->model_type).' #'.$log->model_id : '' }} @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-sm text-gray-400">No activity matches your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
