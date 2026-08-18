@php
    $tool = [
        'title' => 'Profit Margin Calculator',
        'seo_title' => 'Profit Margin Calculator — Margin & Markup | Agency OS',
        'meta_description' => 'Free profit margin calculator: calculate profit, margin percentage and markup from cost and selling price. Essential for agency pricing.',
        'h1' => 'Profit Margin Calculator — Cost, Price, Margin',
        'sub' => 'Enter your cost and selling price to see profit, margin % and markup % instantly.',
        'keywords' => ['profit margin calculator', 'margin calculator', 'markup calculator', 'profit percentage calculator', 'pricing calculator'],
        'howto' => ['Enter the cost of the service or product.', 'Enter the selling price you charge the client.', 'Read your profit, margin percentage and markup percentage.'],
        'faq' => [
            ['What is the difference between margin and markup?', 'Margin is profit ÷ selling price. Markup is profit ÷ cost. A 40% margin equals a 66.7% markup — they are different numbers, and confusing them is a common agency mistake.'],
            ['What margin should a service business target?', 'Service businesses should target 30-50% gross margin. Below 20% you struggle to cover overheads and growth.'],
            ['How do I increase my margin?', 'Raise prices, package services instead of selling hours, and cut tools you do not use. A 5% margin increase on ₹10L revenue is ₹50,000 more profit.'],
        ],
        'content' => '<h2>Margin vs markup — know the difference</h2><p>Margin = (price − cost) ÷ price. Markup = (price − cost) ÷ cost. Agencies that mix these up underprice themselves. This calculator shows both at once.</p>',
    ];
@endphp

@extends('tools._tool-layout')
@section('widget')

<div x-data="profitCalc()">
    <div class="grid sm:grid-cols-2 gap-5">
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Cost (₹)</label>
            <input type="number" x-model.number="cost" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Selling price (₹)</label>
            <input type="number" x-model.number="price" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
    </div>
    <div class="mt-8 grid sm:grid-cols-3 gap-4">
        <div class="rounded-xl bg-gray-50 px-3 py-4 text-center"><div class="text-xs text-gray-500 uppercase">Profit</div><div class="text-2xl font-black text-gray-700 mt-1" x-text="'₹' + profit.toLocaleString('en-IN')"></div></div>
        <div class="rounded-xl bg-indigo-50 px-3 py-4 text-center"><div class="text-xs text-gray-500 uppercase">Margin %</div><div class="text-2xl font-black text-indigo-600 mt-1" x-text="margin + '%'"></div></div>
        <div class="rounded-xl bg-green-50 px-3 py-4 text-center"><div class="text-xs text-gray-500 uppercase">Markup %</div><div class="text-2xl font-black text-green-600 mt-1" x-text="markup + '%'"></div></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function profitCalc() {
    return {
        cost: 50000, price: 80000,
        get profit() { return this.price - this.cost; },
        get margin() { return this.price > 0 ? Math.round(this.profit / this.price * 100) : 0; },
        get markup() { return this.cost > 0 ? Math.round(this.profit / this.cost * 100) : 0; }
    }
}
</script>
@endpush
