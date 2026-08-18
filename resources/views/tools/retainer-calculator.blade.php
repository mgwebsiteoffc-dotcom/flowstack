@php
    $tool = [
    'title' => 'Agency Retainer Calculator',
    'seo_title' => 'Agency Retainer Calculator (2026) — Price Monthly Retainers | Agency OS',
    'meta_description' => 'Free retainer calculator for agencies: estimate the monthly retainer you should charge based on team hours, hourly rate, tools cost and target margin. Includes the retainer pricing formula and examples.',
    'h1' => 'Agency Retainer Calculator — How Much Should You Charge Per Month?',
    'sub' => 'Stop guessing your monthly retainers. Enter your team hours, blended hourly cost and target margin to get a data-backed retainer price in seconds.',
    'keywords' => ['retainer calculator', 'agency retainer pricing', 'monthly retainer rate', 'retainer fee calculator', 'marketing retainer cost'],
    'howto' => ['Enter the number of team hours you will spend on this client each month.', 'Add your blended hourly cost (what your team actually costs you per hour).', 'Set your target margin — most agencies target 35-50%.', 'Include monthly tools cost (ads manager, subscriptions, software).', 'Read your suggested monthly retainer and adjust until the margin feels right.'],
    'faq' => [
        ['What is a good retainer margin for an agency?', 'Most healthy agencies target a 35-50% gross margin on retainers. Below 25% you are effectively working at a loss once overheads are included.'],
        ['How is a retainer price calculated?', 'Retainer = (team hours × blended hourly cost + tools cost) ÷ (1 − target margin). For example: (40h × ₹800 + ₹5,000) ÷ (1 − 0.40) = ₹61,667/month.'],
        ['Should I charge hourly or retainer?', 'Retainers give predictable revenue and let clients budget. They are the industry standard for ongoing services like digital marketing, social media and website management.'],
        ['How do I raise a retainer without losing the client?', 'Show results: reports, metrics and delivered work. Most agencies raise retainers 8-15% annually at renewal with a value conversation, not a price conversation.'],
    ],
    'content' => '<h2>Why every agency needs a retainer calculator</h2><p>Monthly retainers are the backbone of agency revenue — they give you predictable cash flow and let you plan team capacity. But pricing them wrong is expensive: too low and you burn out your team; too high and you lose the deal.</p><h2>The retainer pricing formula</h2><p>The standard formula is simple: <strong>Retainer = (team hours × blended hourly cost + tools cost) ÷ (1 − target margin)</strong>.</p><h2>What is a healthy agency margin?</h2><p>For a digital marketing retainer, a 40% margin is the sweet spot. It covers your overheads (rent, software, management time) and leaves room for the occasional over-servicing month.</p>',
];
@endphp

@extends('tools._tool-layout')
@section('content')


<div x-data="retainerCalc()">
    <div class="grid sm:grid-cols-2 gap-5">
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Team hours per month</label>
            <input type="number" x-model.number="hours" min="1" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Blended hourly cost (₹)</label>
            <input type="number" x-model.number="cost" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Target margin (%)</label>
            <input type="number" x-model.number="margin" min="0" max="90" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Tools cost per month (₹)</label>
            <input type="number" x-model.number="tools" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
    </div>
    <div class="mt-8 rounded-2xl bg-indigo-50 p-6 text-center">
        <div class="text-xs text-gray-500 uppercase tracking-wide">Suggested monthly retainer</div>
        <div class="text-4xl font-black text-indigo-600 mt-2" x-text="'₹' + retainer.toLocaleString('en-IN')"></div>
        <div class="text-xs text-gray-500 mt-2" x-text="'Total cost: ₹' + totalCost.toLocaleString('en-IN') + ' · Profit: ₹' + profit.toLocaleString('en-IN')"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function retainerCalc() {
    return {
        hours: 40, cost: 800, margin: 40, tools: 5000,
        get totalCost() { return (this.hours * this.cost) + this.tools; },
        get retainer() { return Math.round(this.totalCost / (1 - this.margin / 100)); },
        get profit() { return this.retainer - this.totalCost; }
    }
}
</script>
@endpush
