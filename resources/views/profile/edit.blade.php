@extends('layouts.app')
@section('title', 'My profile')
@section('breadcrumb', 'Settings / Profile')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'profile'])</div>
    <div class="lg:col-span-3 space-y-6">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PATCH')
            <x-card title="Profile" icon="user">
                <div class="flex items-center gap-4 mb-5">
                    <x-user-avatar :user="auth()->user()" size="lg" />
                    <div>
                        <input type="file" name="avatar" accept="image/*" class="text-sm">
                        <p class="text-xs text-gray-400 mt-1">jpg, png, webp up to 2MB</p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value="{{ auth()->user()->email }}" disabled class="w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ auth()->user()->phone }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Designation</label>
                        <input type="text" name="designation" value="{{ auth()->user()->designation }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                        <select name="timezone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach (['Asia/Kolkata', 'UTC', 'Asia/Dubai', 'America/New_York', 'Europe/London', 'Australia/Sydney'] as $tz)
                                <option value="{{ $tz }}" {{ auth()->user()->timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select></div>
                </div>
                <button class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save profile</button>
            </x-card>
        </form>

        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            <x-card title="Change password" icon="lock-closed">
                <div class="grid sm:grid-cols-3 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                        <input type="password" name="current_password" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                        <input type="password" name="password" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
                        <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                </div>
                <button class="mt-4 bg-gray-800 text-white px-6 py-2 rounded-lg text-sm">Update password</button>
            </x-card>
        </form>
    </div>
</div>
@endsection
