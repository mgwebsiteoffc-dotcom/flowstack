@extends('layouts.app')
@section('title', 'Users')
@section('breadcrumb', 'Settings / Users')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'users'])</div>
    <div class="lg:col-span-3">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-900">Team users ({{ $users->count() }})</h2>
            @can('create', App\Models\User::class)
                <a href="{{ route('team.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ Invite member</a>
            @endcan
        </div>
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3">User</th><th class="px-4 py-3">Role</th>@if (auth()->user()->canViewFinancials())<th class="px-4 py-3">Hourly cost</th>@endif<th class="px-4 py-3">Status</th><th class="px-4 py-3">Last login</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-user-avatar :user="$user" size="sm" />
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @can('setRole', App\Models\User::class)
                                    <form method="POST" action="{{ route('team.update-role', $user) }}">@csrf @method('PATCH')
                                        <select name="role" onchange="this.form.submit()" class="rounded border border-gray-300 px-2 py-1 text-xs">
                                            @foreach (\App\Models\User::ROLES as $role)
                                                <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>{{ str_replace('_', ' ', $role) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <span class="text-xs bg-gray-100 rounded-full px-2 py-0.5 capitalize">{{ str_replace('_', ' ', $user->role) }}</span>
                                @endcan
                            </td>
                            @if (auth()->user()->canViewFinancials())
                                <td class="px-4 py-3">₹{{ number_format($user->hourly_cost ?? 0) }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <span class="text-xs {{ $user->is_active ? 'text-green-600' : 'text-red-500' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                @can('create', App\Models\User::class)
                                    @if (! $user->email_verified_at)
                                        <form method="POST" action="{{ route('team.resend-invite', $user) }}" class="inline">@csrf
                                            <button class="text-indigo-600 hover:underline block mt-0.5" title="Resend invitation"><x-icon name="arrow-path" class="w-4 h-4 inline-block" /> Resend invite</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
