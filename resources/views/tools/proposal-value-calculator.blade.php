@extends('layouts.site')
@section('title', 'Proposal Value Calculator')
@php
    $seo = ['title' => 'Proposal Value Calculator — Agency OS', 'description' => 'Free proposal pricing calculator: estimate project value from services, hours, rates and markup.'];
@endphp
@section('content')

<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-3xl mx-auto px-6 pt-14 pb-10 text-center">
        <h1 class="text-4xl font-black">Proposal Value Calculator</h1>
        <p class="text-gray-500 mt-3">Price a project before you send the proposal.</p>
    </div>
</header>

<section class="max-w-3xl mx-auto px-6 pb-20" x-data="proposalCalc()">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-lg shadow-gray-100 p-8">
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

        <div class="text-center mt-6">
            <a href="{{ route('register') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">Send proposals free for 14 days</a>
        </div>
    </div>
</section>

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
