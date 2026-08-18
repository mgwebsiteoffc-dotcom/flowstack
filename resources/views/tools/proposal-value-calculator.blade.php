@php
    $tool = [
    'title' => 'Proposal Value Calculator',
    'seo_title' => 'Proposal Value Calculator — Price Agency Projects | Agency OS',
    'meta_description' => 'Free proposal pricing calculator: estimate the value of an agency proposal from services, hours and rates. Perfect for digital marketing and creative agencies.',
    'h1' => 'Proposal Value Calculator — Price Your Next Project',
    'sub' => 'Add the services, hours and rates for any proposal and see the total value instantly.',
    'keywords' => ['proposal value calculator', 'project pricing calculator', 'agency proposal price', 'quote calculator', 'estimate project cost'],
    'howto' => ['Add each service or deliverable you are proposing.', 'Enter the estimated hours for each.', 'Set your hourly rate (or blended rate).', 'Add as many line items as you need.', 'Read the total proposal value and average rate per hour.'],
    'faq' => [
        ['How do agencies price proposals?', 'Most agencies price by (a) hourly × hours, (b) value-based, or (c) a fixed project fee derived from hours plus margin. This calculator uses the hours × rate approach.'],
        ['What is a good average hourly rate for an agency?', 'In India, agency blended rates typically range from ₹800-₹3,000/hour depending on the service. Specialised work (AI automation, Shopify) commands a premium.'],
        ['Should I show line items in a proposal?', 'Yes — itemised proposals convert better. Clients want to see exactly what they are paying for, and it makes scope changes easier to price later.'],
    ],
    'content' => '<h2>Price projects like a pro</h2><p>A well-priced proposal wins the deal and keeps you profitable. Start with hours × rate, add your margin, and present it as an itemised quote — clients trust transparent pricing.</p>',
];
@endphp

@extends('tools._tool-layout')
@section('widget')


<div x-data="proposalCalc()">
    <div class="space-y-3">
        <template x-for="(item, i) in items" :key="i">
            <div class="grid grid-cols-12 gap-2 items-center">
                <input type="text" x-model="item.name" placeholder="Service / deliverable" class="col-span-6 rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" x-model.number="item.hours" placeholder="Hours" min="0" class="col-span-2 rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                <input type="number" x-model.number="item.rate" placeholder="Rate ₹" min="0" class="col-span-3 rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                <button @click="items.splice(i, 1)" class="col-span-1 text-red-400"><x-icon name="x-mark" class="w-4 h-4" /></button>
            </div>
        </template>
        <button @click="items.push({ name: '', hours: 10, rate: 1000 })" class="text-sm text-indigo-600 font-semibold">+ Add service</button>
    </div>
    <div class="mt-8 rounded-2xl bg-purple-50 p-6 text-center">
        <div class="text-xs text-gray-500 uppercase tracking-wide">Proposal value</div>
        <div class="text-4xl font-black text-purple-600 mt-2" x-text="'₹' + total.toLocaleString('en-IN')"></div>
        <div class="text-xs text-gray-500 mt-2" x-text="totalHours + ' hours · ₹' + (total / totalHours).toLocaleString('en-IN', { maximumFractionDigits: 0 }) + ' avg rate/hr'"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function proposalCalc() {
    return {
        items: [{ name: 'Strategy & research', hours: 20, rate: 1500 }, { name: 'Design & build', hours: 40, rate: 1200 }],
        get total() { return this.items.reduce((s, i) => s + (i.hours || 0) * (i.rate || 0), 0); },
        get totalHours() { return this.items.reduce((s, i) => s + (i.hours || 0), 0) || 1; }
    }
}
</script>
@endpush
