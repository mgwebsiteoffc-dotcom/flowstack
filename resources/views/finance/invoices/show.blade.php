@extends('layouts.app')
@section('title', $invoice->invoice_number)
@section('breadcrumb', 'Finance / Invoices / '.$invoice->invoice_number)
@section('content')
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h1>
        <div class="flex items-center gap-2 mt-1">
            <x-status-badge :status="$invoice->status" type="invoice" />
            <x-bb-status :invoice="$invoice" />
            <span class="text-sm text-gray-500">{{ $invoice->client?->company_name }}</span>
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('finance.invoices.pdf', $invoice) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700">⬇ PDF</a>
        @if ($invoice->status === 'draft')
            <form method="POST" action="{{ route('finance.invoices.sync', $invoice) }}">@csrf
                <button class="px-3 py-1.5 text-sm rounded-lg bg-indigo-600 text-white">⟳ Sync to BikriBook</button>
            </form>
            <form method="POST" action="{{ route('finance.invoices.send', $invoice) }}">@csrf
                <button class="px-3 py-1.5 text-sm rounded-lg bg-green-600 text-white">Send to Client</button>
            </form>
        @elseif ($invoice->status === 'sent' || $invoice->status === 'overdue')
            <form method="POST" action="{{ route('finance.invoices.sync', $invoice) }}">@csrf
                <button class="px-3 py-1.5 text-sm rounded-lg bg-indigo-600 text-white">⟳ Check payment status</button>
            </form>
        @endif
        @if ($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
            <button x-data @click="$refs.paidModal.showModal()" class="px-3 py-1.5 text-sm rounded-lg bg-green-50 text-green-700">Mark Paid</button>
        @endif
        <a href="{{ route('finance.invoices.edit', $invoice) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-700">Edit</a>
        <x-confirm-delete :action="route('finance.invoices.destroy', $invoice)" message="Delete this invoice?">
            <x-slot:trigger><span class="px-3 py-1.5 text-sm rounded-lg bg-red-50 text-red-600 cursor-pointer">Delete</span></x-slot:trigger>
        </x-confirm-delete>
    </div>
</div>

<dialog id="paid-modal" x-ref="paidModal" class="rounded-2xl shadow-2xl p-0 w-full max-w-sm">
    <form method="POST" action="{{ route('finance.invoices.mark-paid', $invoice) }}" class="p-6 space-y-3">
        @csrf
        <h3 class="font-semibold text-gray-900">Mark as paid</h3>
        <input type="number" step="0.01" name="paid_amount" value="{{ $invoice->total_amount }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <input type="text" name="payment_method" placeholder="Payment method (e.g. Bank Transfer)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <div class="flex gap-3 justify-end">
            <button type="button" @click="$refs.paidModal.close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
            <button class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg">Confirm</button>
        </div>
    </form>
</dialog>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="Line items" icon="📋">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500 uppercase border-b">
                    <tr><th class="py-2">Description</th><th class="py-2 text-right">Qty</th><th class="py-2 text-right">Rate</th><th class="py-2 text-right">Tax</th><th class="py-2 text-right">Total</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="py-2.5">{{ $item->description }}</td>
                            <td class="py-2.5 text-right">{{ $item->quantity }}</td>
                            <td class="py-2.5 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2.5 text-right">{{ $item->tax_rate }}%</td>
                            <td class="py-2.5 text-right font-medium">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 space-y-1.5 text-sm max-w-xs ml-auto">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₹{{ number_format($invoice->subtotal, 2) }}</span></div>
                @if ($invoice->discount_amount > 0)
                    <div class="flex justify-between"><span class="text-gray-500">Discount</span><span class="text-red-500">-₹{{ number_format($invoice->discount_amount, 2) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-gray-500">Tax ({{ $invoice->tax_rate }}%)</span><span>₹{{ number_format($invoice->tax_amount, 2) }}</span></div>
                <div class="flex justify-between font-bold text-base"><span>Total</span><span>₹{{ number_format($invoice->total_amount, 2) }}</span></div>
                @if ($invoice->status === 'paid')
                    <div class="flex justify-between text-green-600"><span>Paid</span><span>₹{{ number_format($invoice->paid_amount, 2) }}</span></div>
                @else
                    <div class="flex justify-between text-amber-600"><span>Balance due</span><span>₹{{ number_format($invoice->balanceDue(), 2) }}</span></div>
                @endif
            </div>
        </x-card>

        <x-card title="BikriBook sync history" icon="🔗">
            @forelse ($invoice->bikribookSyncLogs as $log)
                <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0 text-sm">
                    <span class="w-6 text-center">{{ $log->status === 'success' ? '✅' : '❌' }}</span>
                    <span class="text-gray-800 flex-1 capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                    <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-3">Not synced to BikriBook yet.</p>
            @endforelse
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card title="Details" icon="ℹ️">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Issue date</dt><dd>{{ $invoice->issue_date->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Due date</dt><dd>{{ $invoice->due_date->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Created by</dt><dd>{{ $invoice->creator?->name }}</dd></div>
                @if ($invoice->sent_at)<div class="flex justify-between"><dt class="text-gray-400">Sent</dt><dd>{{ $invoice->sent_at->format('d M Y H:i') }}</dd></div>@endif
                @if ($invoice->paid_at)<div class="flex justify-between"><dt class="text-gray-400">Paid at</dt><dd>{{ $invoice->payment_date?->format('d M Y') }}</dd></div>@endif
                @if ($invoice->payment_method)<div class="flex justify-between"><dt class="text-gray-400">Method</dt><dd>{{ $invoice->payment_method }}</dd></div>@endif
            </dl>
            @if ($invoice->notes)<p class="text-xs text-gray-500 mt-3 bg-gray-50 rounded-lg p-2">{{ $invoice->notes }}</p>@endif
        </x-card>

        <x-card title="Client" icon="🤝">
            <div class="text-sm text-gray-800 font-medium">{{ $invoice->client?->company_name }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $invoice->client?->address }}</div>
            @php $billing = $invoice->client?->contacts->firstWhere('is_billing_contact', true); @endphp
            @if ($billing)
                <div class="text-xs text-gray-500 mt-1">{{ $billing->name }} · {{ $billing->email }}</div>
            @endif
        </x-card>
    </div>
</div>
@endsection
