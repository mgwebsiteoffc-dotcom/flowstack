@extends('layouts.app')
@section('title', 'Users & roles')
@section('breadcrumb', 'Settings / Users & roles')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'users'])</div>
    <div class="lg:col-span-3 space-y-6">

        <!-- Team users -->
        <x-card title="Team users ({{ $users->count() }})" icon="users">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-gray-500 uppercase border-b">
                        <tr>
                            <th class="px-3 py-2.5">User</th>
                            <th class="px-3 py-2.5">Role</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-3 py-2.5">Active tasks</th>
                            <th class="px-3 py-2.5">Change password</th>
                            <th class="px-3 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <x-user-avatar :user="$user" size="sm" />
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3"><span class="text-xs bg-gray-100 rounded-full px-2 py-0.5 capitalize">{{ str_replace('_', ' ', $user->role) }}</span></td>
                                <td class="px-3 py-3">
                                    <span class="text-xs {{ $user->is_active ? 'text-green-600' : 'text-red-500' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="px-3 py-3 text-gray-600">{{ $user->assigned_tasks_count }}</td>
                                <td class="px-3 py-3">
                                    <details>
                                        <summary class="text-xs text-indigo-600 cursor-pointer hover:underline">Set password</summary>
                                        <form method="POST" action="{{ route('settings.admin-users.password', $user) }}" class="mt-2 flex gap-2">
                                            @csrf
                                            <input type="password" name="password" placeholder="New password" required minlength="8" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                                            <input type="password" name="password_confirmation" placeholder="Confirm" required minlength="8" class="rounded-lg border border-gray-300 px-2 py-1 text-xs">
                                            <button class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-xs">Save</button>
                                        </form>
                                    </details>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('settings.admin-users.toggle', $user) }}" onsubmit="return confirm('Toggle active status for {{ $user->name }}?')">@csrf
                                            <button class="text-xs {{ $user->is_active ? 'text-red-500' : 'text-green-600' }}">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Role menu visibility -->
        <x-card title="What each role can see (sidebar)" icon="eye">
            <p class="text-xs text-gray-400 mb-4">Untick a menu to hide it from that role. This is the tenant-level override — leave everything ticked to use the platform default.</p>
            <form method="POST" action="{{ route('settings.admin-users.role-menus') }}">
                @csrf
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3">Menu</th>
                                @foreach ($roles as $role)
                                    <th class="px-4 py-3 text-center capitalize">{{ str_replace('_', ' ', $role) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($menus as $menu)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 font-medium text-gray-700">{{ $labels[$menu] ?? $menu }}</td>
                                    @foreach ($roles as $role)
                                        @php
                                            $checked = in_array($menu, $menuMap[$role] ?? \App\Support\MenuPermissions::MENUS, true);
                                        @endphp
                                        <td class="px-4 py-2.5 text-center">
                                            <input type="checkbox" name="menus[{{ $role }}][{{ $menu }}]" value="1" class="rounded" {{ $checked ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end mt-4">
                    <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save role visibility</button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
