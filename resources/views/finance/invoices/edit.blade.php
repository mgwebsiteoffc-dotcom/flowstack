@extends('layouts.app')
@section('title', 'Edit '.$invoice->invoice_number)
@section('content')
<form method="POST" action="{{ route('finance.invoices.update', $invoice) }}" class="max-w-4xl space-y-6">
    @csrf
    @method('PATCH')
    <x-card title="Invoice details" icon="receipt">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Client *</label>
                <select name="client_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $invoice->client_id) == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <select name="currency" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="INR" {{ old('currency', $invoice->currency) === 'INR' ? 'selected' : '' }}>₹ INR</option>
                    <option value="USD" {{ old('currency', $invoice->currency) === 'USD' ? 'selected' : '' }}>$ USD</option>
                </select></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Issue date *</label>
                <input type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date->toDateString()) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Due date *</label>
                <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date->toDateString()) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        </div>
    </x-card>

    <x-card title="Line items" icon="clipboard">
        <div x-data="editInvoice()" x-init="init()">
            <input type="hidden" name="tax_rate" :value="taxRate">
            <template x-for="(item, i) in items" :key="i">
                <div class="grid grid-cols-12 gap-2 mb-2 items-center">
                    <input type="text" :name="'items[' + i + '][description]'" x-model="item.description" placeholder="Description"
                           class="col-span-5 rounded-lg border border-gray-300 px-2 py-1.5 text-sm" required>
                    <input type="number" step="0.01" :name="'items[' + i + '][quantity]'" x-model.number="item.quantity" placeholder="Qty"
                           class="col-span-1 rounded-lg border border-gray-300 px-2 py-1.5 text-sm" required min="0.01">
                    <input type="number" step="0.01" :name="'items[' + i + '][unit_price]'" x-model.number="item.unit_price" placeholder="Rate"
                           class="col-span-2 rounded-lg border border-gray-300 px-2 py-1.5 text-sm" required min="0">
                    <input type="number" step="0.01" :name="'items[' + i + '][tax_rate]'" x-model.number="item.tax_rate" placeholder="Tax %"
                           class="col-span-1 rounded-lg border border-gray-300 px-2 py-1.5 text-sm" min="0" max="100">
                    <div class="col-span-2 text-sm text-gray-600 text-right font-medium" x-text="money((item.quantity || 0) * (item.unit_price || 0))"></div>
                    <button type="button" @click="items.splice(i, 1)" class="col-span-1 text-red-400"><x-icon name="x-mark" class="w-4 h-4" /></button>
                </div>
            </template>
            <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: taxRate })" class="text-sm text-indigo-600">+ Add line item</button>
        </div>
    </x-card>

    <x-card title="Notes & terms" icon="pencil-square">
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('notes', $invoice->notes) }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Terms</label>
                <textarea name="terms" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('terms', $invoice->terms) }}</textarea></div>
        </div>
    </x-card>

    <div class="flex justify-end gap-3">
        <a href="{{ route('finance.invoices.show', $invoice) }}" class="px-5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100">Cancel</a>
        <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium">Save changes</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function editInvoice() {
    return {
        items: [],
        taxRate: {{ $invoice->tax_rate }},
        init() {
            this.items = @json($invoice->items->map(fn ($i) => ['description' => $i->description, 'quantity' => (float) $i->quantity, 'unit_price' => (float) $i->unit_price, 'tax_rate' => (float) $i->tax_rate]));
        },
        money(v) { return '₹' + Number(v || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 }); }
    }
}
</script>
@endpush
