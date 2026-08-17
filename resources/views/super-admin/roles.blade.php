@extends('layouts.super-admin')
@section('title', 'Role menu mapping')
@section('content')
<h1 class="text-xl font-bold text-white mb-6">Role menu mapping</h1>
<p class="text-xs text-gray-500 mb-4">Control which menu items each role can see in the sidebar. Unchecked menus are hidden. If no mapping is saved, every role sees everything.</p>

<form method="POST" action="{{ route('super-admin.roles.save') }}">
    @csrf
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-950 text-left text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3">Menu</th>
                    @foreach ($roles as $role)
                        <th class="px-4 py-3 text-center">{{ str_replace('_', ' ', ucfirst($role)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach ($menus as $menu)
                    <tr class="hover:bg-gray-800/40">
                        <td class="px-4 py-2.5 text-gray-200 capitalize">{{ str_replace('-', ' ', $menu) }}</td>
                        @foreach ($roles as $role)
                            @php $allowed = in_array($menu, $map[$role] ?? [], true); @endphp
                            <td class="px-4 py-2.5 text-center">
                                <input type="checkbox" name="menus_{{ $role }}[{{ $menu }}]" value="1" class="rounded" {{ $allowed ? 'checked' : '' }}>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm">Save mapping</button>
    </div>
</form>
@endsection
