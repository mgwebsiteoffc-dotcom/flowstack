@extends('layouts.app')
@section('title', 'New report')
@section('content')
<form method="POST" action="{{ route('reports.store') }}" class="max-w-4xl space-y-6" x-data="{ clientId: {{ old('client_id', 'null') }}, services: [] }"
      @change="if ($event.target.name === 'client_id') { clientId = $event.target.value; fetch('/reports/__services?client_id=' + clientId).then(r => r.json()).then(d => services = d.services); }">
    @csrf
    <x-card title="Step 1 · Basic info" icon="📝">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. July 2026 Performance Report" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                <select name="client_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Select client…</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Report type *</label>
                <select name="report_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach (['weekly', 'monthly', 'quarterly', 'custom'] as $t)
                        <option value="{{ $t }}" {{ old('report_type', 'monthly') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Period start *</label>
                <input type="date" name="period_start" value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Period end *</label>
                <input type="date" name="period_end" value="{{ old('period_end', now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>
    </x-card>

    <x-card title="Step 2 · Metrics" icon="📊">
        <p class="text-xs text-gray-400 mb-4">Sections appear based on the client's services.</p>

        <template x-if="services.includes('digital_marketing')">
            <div class="mb-6">
                <h4 class="font-semibold text-sm text-gray-800 mb-3">📈 Paid Advertising</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach (['ad_spend' => 'Ad spend (₹)', 'impressions' => 'Impressions', 'clicks' => 'Clicks', 'conversions' => 'Conversions', 'revenue' => 'Revenue (₹)'] as $field => $label)
                        <div><label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" name="pa_{{ $field }}" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm"></div>
                    @endforeach
                </div>
                <p class="text-[11px] text-gray-400 mt-2">CTR, ROAS and CPL are auto-calculated.</p>
            </div>
        </template>

        <template x-if="services.includes('shopify_operations')">
            <div class="mb-6">
                <h4 class="font-semibold text-sm text-gray-800 mb-3">🛍️ Shopify</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach (['orders' => 'Orders', 'revenue' => 'Revenue (₹)', 'visitors' => 'Visitors', 'cart_abandonment_rate' => 'Cart abandonment (%)'] as $field => $label)
                        <div><label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" name="sh_{{ $field }}" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm"></div>
                    @endforeach
                </div>
                <p class="text-[11px] text-gray-400 mt-2">AOV and conversion rate are auto-calculated.</p>
            </div>
        </template>

        <template x-if="services.includes('social_media')">
            <div class="mb-6">
                <h4 class="font-semibold text-sm text-gray-800 mb-3">📱 Social Media</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach (['followers_start' => 'Followers (start)', 'followers_end' => 'Followers (end)', 'posts' => 'Posts', 'reach' => 'Reach', 'engagements' => 'Engagements'] as $field => $label)
                        <div><label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" name="sm_{{ $field }}" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm"></div>
                    @endforeach
                </div>
                <p class="text-[11px] text-gray-400 mt-2">Growth % and engagement rate are auto-calculated.</p>
            </div>
        </template>

        <template x-if="services.includes('website_management')">
            <div class="mb-6">
                <h4 class="font-semibold text-sm text-gray-800 mb-3">🌐 Website</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach (['sessions' => 'Sessions', 'users' => 'Users', 'bounce_rate' => 'Bounce rate (%)', 'avg_session_duration' => 'Avg session (sec)', 'goal_completions' => 'Goal completions'] as $field => $label)
                        <div><label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" name="ws_{{ $field }}" class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm"></div>
                    @endforeach
                </div>
            </div>
        </template>
    </x-card>

    <x-card title="Step 3 · Commentary" icon="💬">
        <div class="space-y-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">What worked</label>
                <textarea name="insights" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('insights') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Areas to improve</label>
                <textarea name="recommendations" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('recommendations') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Next priorities</label>
                <textarea name="next_priorities" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('next_priorities') }}</textarea></div>
        </div>
    </x-card>

    <div class="flex justify-end gap-3">
        <a href="{{ route('reports.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button name="status" value="draft" class="bg-gray-800 text-white px-5 py-2 rounded-lg text-sm">Save draft</button>
        <button name="status" value="final" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium">Save & finalize</button>
    </div>
</form>
@endsection
