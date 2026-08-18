@extends('layouts.site')
@section('title', 'Invoice Due Date & GST Calculator')
@php
    $seo = ['title' => 'Invoice Due Date & GST Calculator — Agency OS', 'description' => 'Free invoice calculator: due dates, overdue days, GST amounts and totals for any invoice.'];
@endphp
@section('content')

<header class="bg-gradient-to-b from-indigo-50/60 to-white">
    <div class="max-w-3xl mx-auto px-6 pt-14 pb-10 text-center">
        <h1 class="text-4xl font-black">Invoice Due Date & GST Calculator</h1>
        <p class="text-gray-500 mt-3">Work out due dates, overdue days and GST totals instantly.</p>
    </div>
</header>

<section class="max-w-3xl mx-auto px-6 pb-20" x-data="invoiceCalc()">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-lg shadow-gray-100 p-8">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Invoice amount (₹)</label>
                <input type="number" x-model.number="amount" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">GST rate (%)</label>
                <select x-model.number="gst" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
                    <option value="18">18%</option><option value="12">12%</option><option value="5">5%</option><option value="0">0%</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Issue date</label>
                <input type="date" x-model="issue" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment terms (days)</label>
                <input type="number" x-model.number="terms" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
            </div>
        </div>

        <div class="mt-8 grid sm:grid-cols-2 gap-4">
            <div class="rounded-2xl bg-green-50 p-5 text-center">
                <div class="text-xs text-gray-500 uppercase">GST amount</div>
                <div class="text-2xl font-black text-green-600 mt-1" x-text="'₹' + gstAmount.toLocaleString('en-IN')"></div>
            </div>
            <div class="rounded-2xl bg-indigo-50 p-5 text-center">
                <div class="text-xs text-gray-500 uppercase">Total (incl. GST)</div>
                <div class="text-2xl font-black text-indigo-600 mt-1" x-text="'₹' + total.toLocaleString('en-IN')"></div>
            </div>
            <div class="rounded-2xl bg-blue-50 p-5 text-center">
                <div class="text-xs text-gray-500 uppercase">Due date</div>
                <div class="text-2xl font-black text-blue-600 mt-1" x-text="dueDate"></div>
            </div>
            <div class="rounded-2xl bg-amber-50 p-5 text-center">
                <div class="text-xs text-gray-500 uppercase">Status today</div>
                <div class="text-2xl font-black text-amber-600 mt-1" x-text="status"></div>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('register') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-indigo-700">Create GST invoices free for 14 days</a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
function invoiceCalc() {
    return {
        amount: 100000, gst: 18, issue: new Date().toISOString().slice(0, 10), terms: 15,
        get gstAmount() { return this.amount * this.gst / 100; },
        get total() { return this.amount + this.gstAmount; },
        get dueDate() {
            if (!this.issue) return '—';
            const d = new Date(this.issue); d.setDate(d.getDate() + this.terms);
            return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        },
        get status() {
            if (!this.issue) return '—';
            const d = new Date(this.issue); d.setDate(d.getDate() + this.terms);
            const today = new Date(); today.setHours(0,0,0,0);
            if (d < today) return 'Overdue (' + Math.round((today - d) / 86400000) + ' days)';
            if (d.getTime() === today.getTime()) return 'Due today';
            return 'Due in ' + Math.round((d - today) / 86400000) + ' days';
        }
    }
}
</script>
@endpush
