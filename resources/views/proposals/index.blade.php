@extends('layouts.app')
@section('title', 'Proposals')
@section('breadcrumb', 'Proposals')
@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <form method="GET" class="flex flex-wrap gap-2 text-sm">
        <select name="status" class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach (\App\Models\Proposal::STATUSES as $s)
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
    <a href="{{ route('proposals.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">+ New proposal</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
            <tr><th class="px-4 py-3">Proposal</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Valid until</th><th class="px-4 py-3">Created</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($proposals as $proposal)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('proposals.show', $proposal) }}" class="font-medium text-indigo-600">{{ $proposal->proposal_number }}</a>
                        <div class="text-xs text-gray-400">{{ $proposal->title }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-800">{{ $proposal->client?->company_name }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $proposal->status === 'accepted' ? 'bg-green-100 text-green-700' : ($proposal->status === 'rejected' ? 'bg-red-100 text-red-700' : ($proposal->status === 'sent' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')) }}">
                            {{ ucfirst($proposal->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-medium">₹{{ number_format($proposal->total_amount) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $proposal->valid_until?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $proposal->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state icon="document-text" title="No proposals yet" message="Create a proposal from a lead or directly for a client." :action="route('proposals.create')" actionLabel="New proposal" /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-pagination :paginator="$proposals" />
@endsection
