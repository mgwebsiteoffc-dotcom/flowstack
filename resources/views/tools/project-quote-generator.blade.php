@php
    $tool = [
        'title' => 'Project Quote Generator',
        'seo_title' => 'Project Quote Generator — Instant Client Quotes | Agency OS',
        'meta_description' => 'Free project quote generator for agencies: create a professional client quote with line items, markup and total in seconds.',
        'h1' => 'Project Quote Generator — Build a Client Quote in Seconds',
        'sub' => 'Add line items, apply your markup, and get a clean quote total your client will understand.',
        'keywords' => ['project quote generator', 'client quote template', 'agency quote calculator', 'estimate generator', 'quote maker'],
        'howto' => ['Add each line item for the project.', 'Enter the base cost for each (materials, hours, subcontractors).', 'Set your markup percentage — most agencies use 20-50%.', 'Read your quote total and profit instantly.'],
        'faq' => [
            ['What markup should I use?', 'For project work, 20-50% markup on cost is standard. High-touch or specialised services can command more.'],
            ['What should a quote include?', 'A clear scope, itemised pricing, payment terms (usually 50% advance), delivery timeline and what is excluded.'],
            ['How is a quote different from a proposal?', 'A quote is price-focused; a proposal sells the outcome, includes your approach and typically has the quote inside it. Use both.'],
        ],
        'content' => '<h2>Quote fast, win more</h2><p>Clients compare quotes — the agency that responds fastest with a clear, itemised number often wins. This generator gets you there in seconds.</p>',
    ];
@endphp

@extends('tools._tool-layout')
@section('content')

<div x-data="quoteGen()">
    <div class="space-y-3">
        <template x-for="(item, i) in items" :key="i">
            <div class="grid grid-cols-12 gap-2 items-center">
                <input type="text" x-model="item.name" placeholder="Line item" class="col-span-5 rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" x-model.number="item.cost" placeholder="Cost ₹" min="0" class="col-span-3 rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" x-model.number="item.qty" placeholder="Qty" min="1" class="col-span-2 rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                <button @click="items.splice(i, 1)" class="col-span-2 text-red-400"><x-icon name="x-mark" class="w-4 h-4" /></button>
            </div>
        </template>
        <button @click="items.push({ name: '', cost: 10000, qty: 1 })" class="text-sm text-indigo-600 font-semibold">+ Add line item</button>
    </div>
    <div class="mt-6">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Markup (%)</label>
        <input type="number" x-model.number="markup" min="0" max="200" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
    </div>
    <div class="mt-8 grid sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-gray-50 p-5 text-center"><div class="text-xs text-gray-500 uppercase">Cost</div><div class="text-2xl font-black text-gray-700 mt-1" x-text="'₹' + cost.toLocaleString('en-IN')"></div></div>
        <div class="rounded-2xl bg-green-50 p-5 text-center"><div class="text-xs text-gray-500 uppercase">Profit</div><div class="text-2xl font-black text-green-600 mt-1" x-text="'₹' + profit.toLocaleString('en-IN')"></div></div>
        <div class="rounded-2xl bg-indigo-50 p-5 text-center"><div class="text-xs text-gray-500 uppercase">Quote total</div><div class="text-2xl font-black text-indigo-600 mt-1" x-text="'₹' + total.toLocaleString('en-IN')"></div></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function quoteGen() {
    return {
        items: [{ name: 'Website design', cost: 40000, qty: 1 }, { name: 'Copywriting', cost: 15000, qty: 1 }],
        markup: 30,
        get cost() { return this.items.reduce((s, i) => s + (i.cost || 0) * (i.qty || 1), 0); },
        get profit() { return Math.round(this.cost * this.markup / 100); },
        get total() { return this.cost + this.profit; }
    }
}
</script>
@endpush
