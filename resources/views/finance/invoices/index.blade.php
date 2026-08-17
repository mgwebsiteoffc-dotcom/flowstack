@extends('layouts.app')
@section('title', 'Invoices')
@section('breadcrumb', 'Finance / Invoices')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <form method="GET" class="flex flex-wrap gap-2 text-sm">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice #…" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
        <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach (\App\Models\Invoice::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="client_id" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All clients</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
            @endforeach
        </select>
        <button class="bg-gray-800 text-white px-4 py-1.5 rounded-lg">Filter</button>
    </form>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('finance.invoices.sync-all') }}">@csrf
            <button class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 text-sm">⟳ Sync All</button>
        </form>
        <a href="{{ route('finance.invoices.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New invoice</a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3">Invoice</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">BikriBook</th><th class="px-4 py-3 text-right">Amount</th>
                <th class="px-4 py-3">Issue</th><th class="px-4 py-3">Due</th><th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('finance.invoices.show', $invoice) }}" class="font-medium text-indigo-600">{{ $invoice->invoice_number }}</a>
                        @if ($invoice->bikribook_invoice_number)
                            <div class="text-xs text-gray-400">BB: {{ $invoice->bikribook_invoice_number }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-800">{{ $invoice->client?->company_name }}</td>
                    <td class="px-4 py-3"><x-status-badge :status="$invoice->status" type="invoice" /></td>
                    <td class="px-4 py-3"><x-bb-status :invoice="$invoice" /></td>
                    <td class="px-4 py-3 text-right font-medium">₹{{ number_format($invoice->total_amount) }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $invoice->issue_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-xs {{ $invoice->status === 'overdue' ? 'text-red-600 font-medium' : 'text-gray-500' }}">{{ $invoice->due_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('finance.invoices.pdf', $invoice) }}" class="text-xs text-indigo-600 mr-2" title="Download PDF">PDF</a>
                        @if ($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                            <form method="POST" action="{{ route('finance.invoices.send', $invoice) }}" class="inline">@csrf
                                <button class="text-xs text-green-600 mr-2" title="Send to client">Send</button>
                            </form>
                        @endif
                        <a href="{{ route('finance.invoices.show', $invoice) }}" class="text-xs text-gray-500">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-empty-state icon="🧾" title="No invoices" message="Create your first invoice." :action="route('finance.invoices.create')" actionLabel="New invoice" /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination :paginator="$invoices" />
@endsection
