@extends('layouts.portal')
@section('title', 'Invoices')
@section('content')
<h1 class="text-xl font-bold text-gray-900 mb-5">Invoices</h1>
<div class="bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
    @forelse ($invoices as $invoice)
        <div class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Issued {{ $invoice->issue_date->format('d M Y') }} · Due {{ $invoice->due_date->format('d M Y') }}</div>
            </div>
            <div class="text-sm font-bold text-gray-900">₹{{ number_format($invoice->total_amount) }}</div>
            <span class="text-xs px-2.5 py-1 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">{{ ucfirst($invoice->status) }}</span>
            <a href="{{ route('portal.invoices.download', $invoice) }}" class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg">⬇ PDF</a>
        </div>
    @empty
        <div class="py-12 text-center text-sm text-gray-400">No invoices yet.</div>
    @endforelse
</div>
<x-pagination :paginator="$invoices" />
@endsection
