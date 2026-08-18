@extends('layouts.app')
@section('title', 'My timesheet')
@section('breadcrumb', 'Time / My timesheet')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('time.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">Log</a>
        <a href="{{ route('time.my') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white">My timesheet</a>
        @if (auth()->user()->canAccessFinance())
            <a href="{{ route('time.all') }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">All time</a>
        @endif
    </div>
    <form method="GET" class="flex items-center gap-2 text-sm">
        <input type="week" name="week" value="{{ $weekStart->format('Y-\WW') }}" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Client / Task</th>
                @foreach ($days as $day)
                    <th class="px-2 py-3 text-center {{ $day->isToday() ? 'text-indigo-600' : '' }}">{{ $day->format('D d') }}</th>
                @endforeach
                <th class="px-4 py-3 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach ($entries->groupBy(fn ($e) => $e->client_id) as $clientId => $clientEntries)
                @php $clientName = $clientId ? \App\Models\Client::find($clientId)?->company_name : 'No client'; @endphp
                @foreach ($clientEntries->groupBy('task_id') as $taskId => $taskEntries)
                    <tr>
                        <td class="px-4 py-2.5">
                            <span class="font-medium text-gray-800">{{ $clientName }}</span>
                            <span class="text-xs text-gray-400"> · {{ $taskEntries->first()->task?->title ?? 'Untracked' }}</span>
                        </td>
                        @foreach ($days as $day)
                            @php
                                $dayMinutes = $taskEntries->filter(fn ($e) => $e->started_at?->toDateString() === $day->toDateString())->sum('duration_minutes');
                            @endphp
                            <td class="px-2 py-2.5 text-center {{ $dayMinutes > 0 ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-300' }}">
                                {{ $dayMinutes > 0 ? round($dayMinutes / 60, 1).'h' : '—' }}
                            </td>
                        @endforeach
                        <td class="px-4 py-2.5 text-right font-medium">{{ round($taskEntries->sum('duration_minutes') / 60, 1) }}h</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 font-bold">
            <tr>
                <td class="px-4 py-3">Totals</td>
                @foreach ($days as $day)
                    <td class="px-2 py-3 text-center">{{ round(($grid[$day->toDateString()] ?? collect())->sum('duration_minutes') / 60, 1) }}h</td>
                @endforeach
                <td class="px-4 py-3 text-right">{{ round($totals['minutes'] / 60, 1) }}h</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="grid sm:grid-cols-2 gap-6">
    <x-card title="By client" icon="users">
        @foreach ($byClient as $clientName => $data)
            <div class="flex justify-between py-1.5 text-sm">
                <span class="text-gray-700">{{ $clientName }}</span>
                <span class="font-medium">{{ round($data['minutes'] / 60, 1) }}h</span>
            </div>
        @endforeach
    </x-card>
    <x-card title="Billable vs non-billable" icon="banknotes">
        <div class="flex justify-between py-1.5 text-sm">
            <span class="text-gray-700">Billable</span>
            <span class="font-medium text-green-600">{{ round($totals['billable'] / 60, 1) }}h</span>
        </div>
        <div class="flex justify-between py-1.5 text-sm">
            <span class="text-gray-700">Non-billable</span>
            <span class="font-medium text-gray-500">{{ round(($totals['minutes'] - $totals['billable']) / 60, 1) }}h</span>
        </div>
        <div class="flex justify-between py-1.5 text-sm font-bold border-t mt-2 pt-2">
            <span>Total</span><span>{{ round($totals['minutes'] / 60, 1) }}h</span>
        </div>
    </x-card>
</div>
@endsection
