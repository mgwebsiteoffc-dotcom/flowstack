@extends('layouts.app')
@section('title', 'Notification settings')
@section('breadcrumb', 'Settings / Notifications')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'notifications'])</div>
    <div class="lg:col-span-3">
        <form method="POST" action="{{ route('settings.notifications.save') }}" class="space-y-6">
            @csrf
            <x-card title="Email notifications" icon="bell">
                <p class="text-xs text-gray-400 mb-4">These preferences apply to your account only. Emails are always sent through the queue.</p>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach ($types as $key => $label)
                        <label class="flex items-center gap-2 border rounded-lg px-3 py-2.5 text-sm text-gray-700 cursor-pointer hover:border-indigo-400">
                            <input type="hidden" name="prefs[{{ $key }}]" value="0">
                            <input type="checkbox" name="prefs[{{ $key }}]" value="1" class="rounded" {{ ($prefs[$key] ?? 1) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </x-card>
            <div class="flex justify-end">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save preferences</button>
            </div>
        </form>
    </div>
</div>
@endsection
