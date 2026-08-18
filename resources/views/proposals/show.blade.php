@extends('layouts.app')
@section('title', $proposal->proposal_number)
@section('breadcrumb', 'Proposals / '.$proposal->proposal_number)
@section('content')
<div class="flex items-start justify-between gap-4 mb-5 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $proposal->proposal_number }} — {{ $proposal->title }}</h1>
        <div class="flex items-center gap-2 mt-1">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $proposal->status === 'accepted' ? 'bg-green-100 text-green-700' : ($proposal->status === 'rejected' ? 'bg-red-100 text-red-700' : ($proposal->status === 'sent' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')) }}">
                {{ ucfirst($proposal->status) }}
            </span>
            <a href="{{ route('clients.show', $proposal->client) }}" class="text-sm text-indigo-600">{{ $proposal->client?->company_name }}</a>
            @if ($proposal->lead)<span class="text-xs text-gray-400">from lead: {{ $proposal->lead->contact_name }}</span>@endif
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('proposals.pdf', $proposal) }}" class="px-3 py-1.5 text-sm rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700"><x-icon name="arrow-down-tray" class="w-4 h-4 inline-block" /> PDF</a>
        @if ($proposal->status === 'draft' || $proposal->status === 'sent')
            <form method="POST" action="{{ route('proposals.send', $proposal) }}">@csrf
                <button class="px-3 py-1.5 text-sm rounded-lg bg-green-600 text-white">Send to client</button>
            </form>
        @endif
        @if ($proposal->status !== 'accepted' && $proposal->status !== 'rejected')
            <form method="POST" action="{{ route('proposals.status', $proposal) }}">@csrf
                <input type="hidden" name="status" value="accepted">
                <button class="px-3 py-1.5 text-sm rounded-lg bg-green-50 text-green-700">Mark accepted</button>
            </form>
            <form method="POST" action="{{ route('proposals.status', $proposal) }}">@csrf
                <input type="hidden" name="status" value="rejected">
                <button class="px-3 py-1.5 text-sm rounded-lg bg-red-50 text-red-600">Mark rejected</button>
            </form>
        @endif
        <x-confirm-delete :action="route('proposals.destroy', $proposal)" message="Delete this proposal?">
            <x-slot:trigger><span class="px-3 py-1.5 text-sm rounded-lg bg-red-50 text-red-600 cursor-pointer">Delete</span></x-slot:trigger>
        </x-confirm-delete>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <x-card title="Line items" icon="clipboard">
            <table class="w-full text-sm">
                <thead class="text-left text-xs text-gray-500 uppercase border-b">
                    <tr><th class="py-2">Description</th><th class="py-2 text-right">Qty</th><th class="py-2 text-right">Rate</th><th class="py-2 text-right">Total</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($proposal->items as $item)
                        <tr>
                            <td class="py-2.5">{{ $item->description }}</td>
                            <td class="py-2.5 text-right">{{ $item->quantity }}</td>
                            <td class="py-2.5 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2.5 text-right font-medium">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 space-y-1.5 text-sm max-w-xs ml-auto">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₹{{ number_format($proposal->subtotal, 2) }}</span></div>
                @if ($proposal->discount_amount > 0)
                    <div class="flex justify-between"><span class="text-gray-500">Discount</span><span class="text-red-500">-₹{{ number_format($proposal->discount_amount, 2) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-gray-500">Tax ({{ $proposal->tax_rate }}%)</span><span>₹{{ number_format($proposal->tax_amount, 2) }}</span></div>
                <div class="flex justify-between font-bold text-base"><span>Total</span><span>₹{{ number_format($proposal->total_amount, 2) }}</span></div>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card title="Details" icon="info">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-400">Number</dt><dd>{{ $proposal->proposal_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Client</dt><dd>{{ $proposal->client?->company_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Valid until</dt><dd>{{ $proposal->valid_until?->format('d M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-400">Created by</dt><dd>{{ $proposal->creator?->name }}</dd></div>
                @if ($proposal->sent_at)
                    <div class="flex justify-between"><dt class="text-gray-400">Sent</dt><dd>{{ $proposal->sent_at->format('d M Y H:i') }}</dd></div>
                @endif
            </dl>
            @if ($proposal->notes)<p class="text-xs text-gray-500 mt-3 bg-gray-50 rounded-lg p-2">{{ $proposal->notes }}</p>@endif
            @if ($proposal->terms)<p class="text-xs text-gray-500 mt-2 bg-gray-50 rounded-lg p-2"><strong>Terms:</strong> {{ $proposal->terms }}</p>@endif
        </x-card>
        @if ($proposal->status === 'accepted' && $proposal->lead && ! $proposal->lead->converted_to_client_id)
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                Proposal accepted! <a href="{{ route('leads.convert', $proposal->lead) }}" class="font-medium underline">Convert this lead to a client</a>.
            </div>
        @endif
    </div>
</div>
@endsection
