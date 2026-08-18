@extends('layouts.site')
@section('title', 'Retainer Calculator')
@php
    $seo = ['title' => 'Agency Retainer Calculator — Agency OS', 'description' => 'Free retainer calculator: estimate the monthly retainer you should charge based on team hours, hourly rate and target margin.'];
@endphp
@section('content')

<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-3xl mx-auto px-6 pt-14 pb-10 text-center">
        <h1 class="text-4xl font-black">Agency Retainer Calculator</h1>
        <p class="text-gray-500 mt-3">Estimate the monthly retainer to charge — based on the hours your team will actually spend.</p>
    </div>
</header>

<section class="max-w-3xl mx-auto px-6 pb-20" x-data="retainerCalc()">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-lg shadow-gray-100 p-8">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Team hours per month</label>
                <input type="number" x-model.number="hours" min="1" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Blended hourly cost (₹)</label>
                <input type="number" x-model.number="cost" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Target margin (%)</label>
                <input type="number" x-model.number="margin" min="0" max="90" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tools cost per month (₹)</label>
                <input type="number" x-model.number="tools" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mt-8 rounded-2xl bg-indigo-50 p-6 text-center">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Suggested monthly retainer</div>
            <div class="text-4xl font-black text-indigo-600 mt-2" x-text="'₹' + retainer.toLocaleString('en-IN')"></div>
            <div class="text-xs text-gray-500 mt-2" x-text="'Total cost: ₹' + totalCost.toLocaleString('en-IN') + ' · Profit: ₹' + profit.toLocaleString('en-IN')"></div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('register') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">Manage retainers free for 14 days</a>
        </div>
    </div>
</section>

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
