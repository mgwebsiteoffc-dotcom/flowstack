@php
    $tool = [
        'title' => 'Agency Margin Calculator',
        'seo_title' => 'Agency Margin Calculator — Gross & Net Margin | Agency OS',
        'meta_description' => 'Free agency margin calculator: work out your gross and net margin from revenue, team cost and overheads. Know if your agency is actually profitable.',
        'h1' => 'Agency Margin Calculator — Are You Actually Profitable?',
        'sub' => 'Revenue is vanity, margin is sanity. Enter your numbers and see your real agency margin in seconds.',
        'keywords' => ['agency margin calculator', 'agency profit margin', 'gross margin calculator', 'agency profitability', 'net margin calculator'],
        'howto' => ['Enter your monthly revenue (all client income).', 'Enter team cost (salaries + freelancers for delivery).', 'Enter tools & software cost.', 'Enter other overheads (rent, marketing, admin).', 'Read your gross margin, net margin and profit.'],
        'faq' => [
            ['What is a good margin for an agency?', 'A healthy digital agency runs a 40-55% gross margin and a 15-25% net margin. Below 10% net margin, one bad month can wipe out your year.'],
            ['Gross vs net margin — what is the difference?', 'Gross margin excludes overheads: (revenue − delivery cost) ÷ revenue. Net margin subtracts everything: (revenue − all costs) ÷ revenue. Track both.'],
            ['How can I improve agency margins?', 'Raise retainers, cut low-margin services, track team utilisation, and automate reporting so your team spends time on billable work.'],
        ],
        'content' => '<h2>Know your real margin</h2><p>Most agencies think they are profitable until they add up the tools, the free work and the management time. This calculator gives you the honest number.</p><p>In Agency OS, the Profitability page tracks margin per client automatically from your time entries and expenses.</p>',
    ];
@endphp

@extends('tools._tool-layout')
@section('content')

<div x-data="marginCalc()">
    <div class="grid sm:grid-cols-2 gap-5">
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Monthly revenue (₹)</label>
            <input type="number" x-model.number="revenue" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Team cost (₹)</label>
            <input type="number" x-model.number="team" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Tools & software (₹)</label>
            <input type="number" x-model.number="tools" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Other overheads (₹)</label>
            <input type="number" x-model.number="overhead" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
    </div>
    <div class="mt-8 grid sm:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-indigo-50 p-5 text-center"><div class="text-xs text-gray-500 uppercase">Gross margin</div><div class="text-3xl font-black text-indigo-600 mt-1" x-text="grossMargin + '%'"></div></div>
        <div class="rounded-2xl bg-green-50 p-5 text-center"><div class="text-xs text-gray-500 uppercase">Net margin</div><div class="text-3xl font-black text-green-600 mt-1" x-text="netMargin + '%'"></div></div>
        <div class="rounded-2xl bg-purple-50 p-5 text-center"><div class="text-xs text-gray-500 uppercase">Net profit</div><div class="text-3xl font-black text-purple-600 mt-1" x-text="'₹' + profit.toLocaleString('en-IN')"></div></div>
        <div class="rounded-2xl p-5 text-center" :class="netMargin >= 15 ? 'bg-green-50' : (netMargin >= 5 ? 'bg-amber-50' : 'bg-red-50')">
            <div class="text-xs text-gray-500 uppercase">Health</div>
            <div class="text-3xl font-black mt-1" :class="netMargin >= 15 ? 'text-green-600' : (netMargin >= 5 ? 'text-amber-600' : 'text-red-600')" x-text="health"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function marginCalc() {
    return {
        revenue: 500000, team: 200000, tools: 30000, overhead: 50000,
        get grossMargin() { return this.revenue > 0 ? Math.round((this.revenue - this.team) / this.revenue * 100) : 0; },
        get profit() { return this.revenue - this.team - this.tools - this.overhead; },
        get netMargin() { return this.revenue > 0 ? Math.round(this.profit / this.revenue * 100) : 0; },
        get health() { return this.netMargin >= 15 ? 'Healthy' : (this.netMargin >= 5 ? 'Watch' : 'At risk'); }
    }
}
</script>
@endpush
