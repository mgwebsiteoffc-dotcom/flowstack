@extends('layouts.app')
@section('title', 'New proposal')
@section('content')
<form method="POST" action="{{ route('proposals.store') }}" class="max-w-4xl space-y-6">
    @csrf
    <x-card title="Proposal details" icon="document-text">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $lead ? 'Proposal for '.$lead->contact_name : '') }}" required placeholder="e.g. Digital Marketing Retainer Proposal" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                <div class="flex gap-2 items-start">
                            <select name="client_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Select client…</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $lead?->converted_to_client_id) == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                    @endforeach
                </select>
<x-quick-client-add target="client_id" /></div>

            </div>
            @if ($lead)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lead</label>
                    <input type="text" value="{{ $lead->contact_name }} ({{ $lead->company_name }})" disabled class="w-full rounded-lg bg-gray-50 border border-gray-300 px-3 py-2 text-sm text-gray-500">
                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valid until</label>
                <input type="date" name="valid_until" value="{{ old('valid_until', now()->addDays(15)->toDateString()) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <select name="currency" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="INR" {{ old('currency') === 'INR' || ! old('currency') ? 'selected' : '' }}>₹ INR</option>
                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>$ USD</option>
                    <option value="AED" {{ old('currency') === 'AED' ? 'selected' : '' }}>AED</option>
                </select>
            </div>
        </div>
    </x-card>

    <x-card title="Services & pricing" icon="banknotes">
        <div x-data="proposalBuilder()" x-init="init()">
            <template x-for="(item, i) in items" :key="i">
                <div class="grid grid-cols-12 gap-2 mb-2 items-center">
                    <input type="text" :name="'items[' + i + '][description]'" x-model="item.description" placeholder="Service description"
                           class="col-span-7 rounded-lg border border-gray-300 px-2 py-1.5 text-sm" required>
                    <input type="number" step="0.01" :name="'items[' + i + '][quantity]'" x-model.number="item.quantity" placeholder="Qty"
                           class="col-span-1 rounded-lg border border-gray-300 px-2 py-1.5 text-sm" required min="0.01">
                    <input type="number" step="0.01" :name="'items[' + i + '][unit_price]'" x-model.number="item.unit_price" placeholder="Rate"
                           class="col-span-2 rounded-lg border border-gray-300 px-2 py-1.5 text-sm" required min="0">
                    <div class="col-span-1 text-sm text-gray-600 text-right font-medium" x-text="money((item.quantity || 0) * (item.unit_price || 0))"></div>
                    <button type="button" @click="items.splice(i, 1)" class="col-span-1 text-red-400 hover:text-red-600"><x-icon name="x-mark" class="w-4 h-4" /></button>
                </div>
            </template>
            <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: 0 })" class="text-sm text-indigo-600">+ Add line</button>
            <div class="border-t mt-4 pt-4 space-y-2 text-sm max-w-xs ml-auto">
                <input type="hidden" name="tax_rate" :value="taxRate">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-medium" x-text="money(subtotal)"></span></div>
                <div class="flex justify-between items-center gap-2">
                    <span class="text-gray-500">Discount</span>
                    <span class="flex items-center gap-1">
                        <select name="discount_type" x-model="discountType" class="rounded border border-gray-300 px-1.5 py-1 text-xs">
                            <option value="">None</option>
                            <option value="percentage">%</option>
                            <option value="fixed">Fixed</option>
                        </select>
                        <input type="number" step="0.01" name="discount_value" x-model.number="discountValue" min="0" class="w-20 rounded border border-gray-300 px-1.5 py-1 text-xs">
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-gray-500">Tax (GST)</span><span class="font-medium" x-text="money(taxAmount)"></span></div>
                <div class="flex justify-between text-base font-bold"><span>Total</span><span x-text="money(total)"></span></div>
            </div>
        </div>
    </x-card>

    <x-card title="Notes & terms" icon="clipboard">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('notes') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Terms</label>
                <textarea name="terms" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('terms', 'Proposal valid for 15 days. 50% advance to begin work.') }}</textarea></div>
        </div>
    </x-card>

    <div class="flex justify-end gap-3">
        <a href="{{ route('proposals.index') }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Create proposal</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function proposalBuilder() {
    return {
        items: [],
        taxRate: {{ app('currentTenant')->settings['tax_rate'] ?? 18 }},
        discountType: '',
        discountValue: 0,
        init() { this.items.push({ description: '', quantity: 1, unit_price: 0 }); },
        get subtotal() { return this.items.reduce((s, i) => s + (i.quantity || 0) * (i.unit_price || 0), 0); },
        get discountAmount() {
            if (this.discountType === 'percentage') return this.subtotal * (this.discountValue || 0) / 100;
            if (this.discountType === 'fixed') return Math.min(this.discountValue || 0, this.subtotal);
            return 0;
        },
        get taxAmount() { return (this.subtotal - this.discountAmount) * this.taxRate / 100; },
        get total() { return this.subtotal - this.discountAmount + this.taxAmount; },
        money(v) { return '₹' + Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 }); }
    }
}
</script>
@endpush
