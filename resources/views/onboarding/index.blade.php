@extends('layouts.app')
@section('title', 'Onboarding')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Set up your workspace</h1>
        <p class="text-sm text-gray-500 mt-1">Step {{ $step }} of 6</p>
        <div class="flex justify-center gap-1.5 mt-4">
            @for ($i = 1; $i <= 6; $i++)
                <div class="h-1.5 w-8 rounded-full {{ $i <= $step ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
            @endfor
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        @if ($step === 1)
            <h2 class="text-lg font-semibold mb-4"><x-icon name="building-office" class="w-4 h-4 inline-block" /> Basic setup</h2>
            <form method="POST" action="{{ route('onboarding.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="step" value="1">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agency logo</label>
                    <input type="file" name="logo" accept="image/*" class="text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                        <select name="timezone" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach (['Asia/Kolkata', 'UTC', 'Asia/Dubai', 'America/New_York', 'Europe/London', 'Australia/Sydney'] as $tz)
                                <option value="{{ $tz }}" {{ ($tenant->settings['timezone'] ?? 'Asia/Kolkata') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select name="currency" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach (['INR' => '₹ INR', 'USD' => '$ USD', 'AED' => 'AED', 'GBP' => '£ GBP', 'EUR' => '€ EUR'] as $code => $label)
                                <option value="{{ $code }}" {{ ($tenant->settings['currency'] ?? 'INR') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Save & continue →</button>
            </form>

        @elseif ($step === 2)
            <h2 class="text-lg font-semibold mb-4"><x-icon name="users" class="w-4 h-4 inline-block" /> Invite your team</h2>
            <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="step" value="2">
                <div x-data="{ rows: [{ email: '', role: 'specialist' }] }">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="flex gap-3 mb-2">
                            <input type="email" :name="'emails[' + i + ']'" x-model="row.email" placeholder="teammate@agency.com"
                                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <select :name="'roles[' + i + ']'" x-model="row.role" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="ops_manager">Ops Manager</option>
                                <option value="account_manager">Account Manager</option>
                                <option value="specialist">Specialist</option>
                            </select>
                            <button type="button" @click="rows.splice(i, 1)" class="text-red-400">×</button>
                        </div>
                    </template>
                    <button type="button" @click="rows.push({ email: '', role: 'specialist' })" class="text-sm text-indigo-600">+ Add another</button>
                </div>
                <div class="flex gap-3">
                    <button class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Invite & continue →</button>
                    <button name="skip" value="1" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Skip</button>
                </div>
            </form>

        @elseif ($step === 3)
            <h2 class="text-lg font-semibold mb-4"><x-icon name="users" class="w-4 h-4 inline-block" /> Add your first client</h2>
            <p class="text-sm text-gray-500 mb-4">You can add clients later from the Clients section.</p>
            <div class="flex gap-3">
                <a href="{{ route('clients.create') }}" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Add client →</a>
                <form method="POST" action="{{ route('onboarding.store') }}">@csrf<input type="hidden" name="step" value="3">
                    <button class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Skip</button>
                </form>
            </div>

        @elseif ($step === 4)
            <h2 class="text-lg font-semibold mb-4"><x-icon name="link" class="w-4 h-4 inline-block" /> Connect Lead365</h2>
            <p class="text-sm text-gray-500 mb-4">Paste this webhook URL into your Lead365 account and select the events to send:</p>
            <div x-data="{ copied: false }">
                <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <code class="text-xs text-indigo-700 break-all flex-1">{{ url('webhooks/lead365/'.$tenant->slug) }}</code>
                    <button type="button" @click="navigator.clipboard.writeText('{{ url('webhooks/lead365/'.$tenant->slug) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg" x-text="copied ? 'Copied check-circle' : 'Copy'"></button>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-gray-600">
                @foreach (['lead.created', 'lead.updated', 'lead.deleted', 'lead.stage_changed', 'lead.assigned', 'lead.won', 'lead.lost', 'form.submitted', 'meta.lead.received'] as $event)
                    <div class="flex items-center gap-2 bg-gray-50 rounded px-2 py-1"><span class="text-green-600"></span><code>{{ $event }}</code></div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('onboarding.store') }}" class="mt-6">@csrf
                <input type="hidden" name="step" value="4">
                <button class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Continue →</button>
            </form>

        @elseif ($step === 5)
            <h2 class="text-lg font-semibold mb-4"><x-icon name="receipt" class="w-4 h-4 inline-block" /> Connect BikriBook</h2>
            <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="step" value="5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BikriBook API key</label>
                    <input type="password" name="bikribook_api_key" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Paste your API key (stored encrypted)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Base URL <span class="text-gray-400">(optional)</span></label>
                    <input type="url" name="bikribook_base_url" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="https://api.bikribook.com/v1">
                </div>
                <div class="flex gap-3">
                    <button class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Save & continue →</button>
                    <button name="skip" value="1" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Skip for now</button>
                </div>
            </form>

        @else
            <h2 class="text-lg font-semibold mb-4"><x-icon name="sparkles" class="w-4 h-4 inline-block" /> Almost done</h2>
            <p class="text-sm text-gray-500 mb-6">You can always change these later in Settings.</p>
            <form method="POST" action="{{ route('onboarding.store') }}">@csrf<input type="hidden" name="step" value="6">
                <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium">Finish setup →</button>
            </form>
        @endif
    </div>
</div>
@endsection
