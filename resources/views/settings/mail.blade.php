@extends('layouts.app')
@section('title', 'Email (Gmail SMTP)')
@section('breadcrumb', 'Settings / Email (Gmail SMTP)')
@section('content')
<div class="grid lg:grid-cols-4 gap-6">
    <div>@include('settings.partials.nav', ['active' => 'mail'])</div>
    <div class="lg:col-span-3 space-y-6">

        <x-card title="Gmail SMTP" icon="envelope">
            <p class="text-xs text-gray-500 mb-4">
                All outgoing mail (welcome emails, invoices, reports, portal invites, alerts) is sent from this
                Gmail account. Use an <strong>App Password</strong> - not your normal Gmail password.
            </p>

            <form method="POST" action="{{ route('settings.mail.save') }}">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mailer</label>
                        <select name="mailer" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="smtp" {{ $mailer === 'smtp' ? 'selected' : '' }}>SMTP (send real email)</option>
                            <option value="log" {{ $mailer === 'log' ? 'selected' : '' }}>Log only (dev, no sending)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From name</label>
                        <input type="text" name="from_name" value="{{ $fromName }}" placeholder="Task365"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SMTP host</label>
                        <input type="text" name="host" value="{{ $host }}" placeholder="smtp.gmail.com"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                        <input type="number" name="port" value="{{ $port }}" placeholder="587"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gmail address (username)</label>
                        <input type="text" name="username" value="{{ $username }}" placeholder="you@gmail.com"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">App password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep the current one"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                        <select name="encryption" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="tls" {{ $encryption === 'tls' ? 'selected' : '' }}>TLS (port 587)</option>
                            <option value="ssl" {{ $encryption === 'ssl' ? 'selected' : '' }}>SSL (port 465)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From address</label>
                        <input type="email" name="from_address" value="{{ $fromAddress }}" placeholder="you@gmail.com"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <button class="bg-indigo-600 text-white rounded-lg px-5 py-2 text-sm font-semibold">Save mail settings</button>
            </form>
        </x-card>

        <x-card title="Send a test email" icon="check-circle">
            <p class="text-xs text-gray-500 mb-3">
                Sends a real email from the configured account to your inbox. Any SMTP error (wrong password,
                app password not enabled, blocked sign-in) is shown here.
            </p>
            <form method="POST" action="{{ route('settings.mail.test') }}">
                @csrf
                @error('test')
                    <div class="mb-3 text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2">{{ $message }}</div>
                @enderror
                <button class="bg-gray-800 text-white rounded-lg px-5 py-2 text-sm font-semibold">Send test email</button>
            </form>
        </x-card>

        <x-card title="How to get a Gmail app password" icon="shield-check">
            <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                <li>Sign in to your Google account and turn on <strong>2-Step Verification</strong> (Account &rarr; Security &rarr; 2-Step Verification).</li>
                <li>Open <strong>Security &rarr; App passwords</strong> (search "App passwords" in your Google account).</li>
                <li>Create an app password for "Mail" - Google shows a 16-character code.</li>
                <li>Paste that code (spaces optional) in the <strong>App password</strong> field above and save.</li>
                <li>Click <strong>Send test email</strong>. If Gmail rejects it, wait a minute and check that the app password was created for the same account.</li>
            </ol>
            <p class="text-xs text-gray-400 mt-3">
                Works with free Gmail and Google Workspace accounts. Port 587 + TLS is the default; use port 465 + SSL if your network blocks it.
            </p>
        </x-card>

    </div>
</div>
@endsection
