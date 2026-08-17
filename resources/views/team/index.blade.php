@extends('layouts.app')
@section('title', 'Team')
@section('breadcrumb', 'Team')
@section('content')
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-gray-900">Team ({{ $users->count() }})</h2>
    @can('create', App\Models\User::class)
        <button x-data @click="$refs.inviteModal.showModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ Invite member</button>
    @endcan
</div>

<dialog id="invite-modal" x-ref="inviteModal" class="rounded-2xl shadow-2xl p-0 w-full max-w-md">
    <form method="POST" action="{{ route('team.invite') }}" class="p-6 space-y-4">
        @csrf
        <h3 class="font-semibold text-gray-900">Invite a team member</h3>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
            <input type="text" name="name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select name="role" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                @foreach (['ops_manager' => 'Ops Manager', 'account_manager' => 'Account Manager', 'specialist' => 'Specialist'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3 justify-end">
            <button type="button" @click="$refs.inviteModal.close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
            <button class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg">Send invite</button>
        </div>
    </form>
</dialog>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ($users as $member)
        <a href="{{ route('team.show', $member) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-400 transition">
            <div class="flex items-center gap-3">
                <x-user-avatar :user="$member" size="lg" />
                <div class="min-w-0">
                    <div class="font-semibold text-gray-900 truncate">{{ $member->name }}</div>
                    <div class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', $member->role) }}</div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                <div><div class="font-bold text-gray-800">{{ $member->assigned_tasks_count }}</div><div class="text-[10px] text-gray-400">Active</div></div>
                <div><div class="font-bold {{ ($member->overdue_count ?? 0) > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $member->overdue_count ?? 0 }}</div><div class="text-[10px] text-gray-400">Overdue</div></div>
                <div><div class="font-bold text-gray-800">{{ $workloadByUser[$member->id]['hours'] ?? 0 }}h</div><div class="text-[10px] text-gray-400">This week</div></div>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full mt-3">
                <div class="h-1.5 {{ ($member->overdue_count ?? 0) > 3 ? 'bg-red-500' : 'bg-indigo-500' }} rounded-full"
                     style="width: {{ min(100, ($workloadByUser[$member->id]['active'] ?? 0) * 12) }}%"></div>
            </div>
        </a>
    @endforeach
</div>

<div class="mt-6">
    <x-card title="Capacity planning — open tasks due per day (next 7 days)" icon="calendar-days" :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Member</th>
                        @foreach ($capacityDays as $day)
                            <th class="px-3 py-2.5 text-center {{ $day->isToday() ? 'text-indigo-600' : '' }}">{{ $day->format('D d') }}</th>
                        @endforeach
                        <th class="px-4 py-2.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($users as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-medium text-gray-800">{{ $member->name }}</td>
                            @php $memberTotal = 0; @endphp
                            @foreach ($capacityDays as $day)
                                @php
                                    $count = $capacity[$member->id][$day->toDateString()] ?? 0;
                                    $memberTotal += $count;
                                @endphp
                                <td class="px-3 py-2.5 text-center">
                                    <span class="inline-block min-w-[22px] rounded-full px-1.5 py-0.5 text-xs {{ $count === 0 ? 'text-gray-300' : ($count > 4 ? 'bg-red-100 text-red-700' : ($count > 2 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700')) }}">{{ $count }}</span>
                                </td>
                            @endforeach
                            <td class="px-4 py-2.5 text-right font-medium">{{ $memberTotal }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 text-xs text-gray-400 border-t">Colours: green ≤ 2 · amber 3–4 · red 5+ tasks due that day.</div>
    </x-card>
</div>
@endsection
