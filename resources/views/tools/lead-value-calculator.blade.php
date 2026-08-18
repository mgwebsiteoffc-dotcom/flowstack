@php
    $tool = [
        'title' => 'Lead Value Calculator',
        'seo_title' => 'Lead Value Calculator — What Is a Lead Worth? | Agency OS',
        'meta_description' => 'Free lead value calculator for agencies: estimate what each lead is worth based on conversion rate, average deal value and win rate.',
        'h1' => 'Lead Value Calculator — What Is Each Lead Worth?',
        'sub' => 'Know exactly how much you can afford to spend acquiring a lead. Enter your numbers and see the lead value instantly.',
        'keywords' => ['lead value calculator', 'lead worth calculator', 'customer acquisition cost', 'lead value formula', 'marketing ROI calculator'],
        'howto' => ['Enter the number of leads you receive per month.', 'Enter how many of those become clients (conversion rate).', 'Enter your average client deal value (annual or one-time).', 'Read the value of a single lead and the maximum you should pay per lead.'],
        'faq' => [
            ['How do you calculate lead value?', 'Lead value = (deal value × conversion rate). For example, if your average client is worth ₹1,00,000 and you convert 20% of leads, each lead is worth ₹20,000.'],
            ['What should I spend per lead?', 'As a rule, spend no more than 20-30% of lead value on acquisition. If a lead is worth ₹20,000, your cost per lead should stay under ₹4,000-₹6,000.'],
            ['Why is lead value important?', 'It tells you which channels are profitable. If Meta Ads cost ₹5,000 per lead but your lead value is ₹8,000, you are winning. If it is ₹3,000, you are losing money on every click.'],
        ],
        'content' => '<h2>Turn leads into a number you can optimise</h2><p>Most agencies track lead counts but not lead <em>value</em>. Once you know what a lead is worth, every marketing decision becomes clear: what to bid, which channels to scale, and when to stop spending.</p><p>Combine this with a CRM pipeline (like the one built into Agency OS) and you will always know your numbers.</p>',
    ];
@endphp

@extends('tools._tool-layout')
@section('content')

<div x-data="leadValueCalc()">
    <div class="grid sm:grid-cols-2 gap-5">
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Leads per month</label>
            <input type="number" x-model.number="leads" min="1" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Conversion rate (%)</label>
            <input type="number" x-model.number="conv" min="0" max="100" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Average deal value (₹)</label>
            <input type="number" x-model.number="deal" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Cost per lead (₹)</label>
            <input type="number" x-model.number="cpl" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
    </div>
    <div class="mt-8 grid sm:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-indigo-50 p-5 text-center">
            <div class="text-xs text-gray-500 uppercase">Value per lead</div>
            <div class="text-3xl font-black text-indigo-600 mt-1" x-text="'₹' + valuePerLead.toLocaleString('en-IN')"></div>
        </div>
        <div class="rounded-2xl bg-green-50 p-5 text-center">
            <div class="text-xs text-gray-500 uppercase">ROI per lead</div>
            <div class="text-3xl font-black text-green-600 mt-1" x-text="roi + 'x'"></div>
        </div>
        <div class="rounded-2xl bg-purple-50 p-5 text-center">
            <div class="text-xs text-gray-500 uppercase">Clients per month</div>
            <div class="text-3xl font-black text-purple-600 mt-1" x-text="clients"></div>
        </div>
        <div class="rounded-2xl bg-amber-50 p-5 text-center">
            <div class="text-xs text-gray-500 uppercase">Max you should pay per lead</div>
            <div class="text-3xl font-black text-amber-600 mt-1" x-text="'₹' + maxCpl.toLocaleString('en-IN')"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function leadValueCalc() {
    return {
        leads: 100, conv: 20, deal: 100000, cpl: 5000,
        get clients() { return Math.round(this.leads * this.conv / 100); },
        get valuePerLead() { return Math.round(this.deal * this.conv / 100); },
        get roi() { return this.cpl > 0 ? (this.valuePerLead / this.cpl).toFixed(1) : '∞'; },
        get maxCpl() { return Math.round(this.valuePerLead * 0.3); }
    }
}
</script>
@endpush
