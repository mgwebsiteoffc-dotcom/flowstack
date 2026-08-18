@php
    $tool = [
    'title' => 'Invoice Due Date & GST Calculator',
    'seo_title' => 'Invoice Due Date & GST Calculator — Due Dates, GST Amount, Total | Agency OS',
    'meta_description' => 'Free invoice calculator: calculate GST amount, total including GST, due date and overdue status for any invoice. Perfect for freelancers and agencies in India.',
    'h1' => 'Invoice Due Date & GST Calculator',
    'sub' => 'Know exactly when an invoice is due, how much GST to charge and whether it is overdue — in one click.',
    'keywords' => ['invoice due date calculator', 'GST calculator', 'invoice GST amount', 'invoice due date', 'overdue invoice calculator'],
    'howto' => ['Enter the invoice amount (before GST).', 'Pick the GST rate — 18%, 12%, 5% or 0%.', 'Choose the issue date.', 'Set your payment terms in days (most agencies use 15-30 days).', 'Read the GST amount, total, due date and current status instantly.'],
    'faq' => [
        ['What GST rate should I charge on agency services?', 'Most agency services (marketing, creative, website management) fall under 18% GST in India. Some services like advertising space can be 5%. Confirm with your CA.'],
        ['How do I calculate GST on an invoice?', 'GST amount = invoice amount × GST rate ÷ 100. Total = invoice amount + GST. For ₹1,00,000 at 18%: GST is ₹18,000 and the total is ₹1,18,000.'],
        ['When is an invoice considered overdue?', 'An invoice is overdue the day after its due date (issue date + payment terms). Late payment reminders usually start 3-7 days after the due date.'],
        ['What are standard payment terms for agencies?', 'Most agencies use Net 15 or Net 30. In India, 15 days is common for retainers, and 30 days for project-based work.'],
    ],
    'content' => '<h2>GST invoicing made simple</h2><p>If you bill clients in India, you need to charge GST correctly on every invoice. This calculator gives you the GST amount, the total including GST, and the due date — so your invoices are always accurate.</p><h2>Payment terms that actually get you paid</h2><p>Clear payment terms reduce late payments. State them on every invoice and send a reminder 3 days before the due date. Agencies using automated reminders get paid 2x faster.</p>',
];
@endphp

@extends('tools._tool-layout')
@section('widget')


<div x-data="invoiceCalc()">
    <div class="grid sm:grid-cols-2 gap-5">
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Invoice amount (₹)</label>
            <input type="number" x-model.number="amount" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">GST rate (%)</label>
            <select x-model.number="gst" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="18">18%</option><option value="12">12%</option><option value="5">5%</option><option value="0">0%</option>
            </select></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Issue date</label>
            <input type="date" x-model="issue" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Payment terms (days)</label>
            <input type="number" x-model.number="terms" min="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></div>
    </div>
    <div class="mt-8 grid sm:grid-cols-2 gap-4">
        <div class="rounded-xl bg-green-50 px-3 py-4 text-center"><div class="text-xs text-gray-500 uppercase">GST amount</div><div class="text-2xl font-black text-green-600 mt-1" x-text="'₹' + gstAmount.toLocaleString('en-IN')"></div></div>
        <div class="rounded-xl bg-indigo-50 px-3 py-4 text-center"><div class="text-xs text-gray-500 uppercase">Total (incl. GST)</div><div class="text-2xl font-black text-indigo-600 mt-1" x-text="'₹' + total.toLocaleString('en-IN')"></div></div>
        <div class="rounded-xl bg-blue-50 px-3 py-4 text-center"><div class="text-xs text-gray-500 uppercase">Due date</div><div class="text-2xl font-black text-blue-600 mt-1" x-text="dueDate"></div></div>
        <div class="rounded-xl bg-amber-50 px-3 py-4 text-center"><div class="text-xs text-gray-500 uppercase">Status today</div><div class="text-2xl font-black text-amber-600 mt-1" x-text="status"></div></div>
    </div>
</div>
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
